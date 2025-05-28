import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var assignStudentListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        var form_data = $('#studentSearchForm').serialize();

        let tableContent = new Tabulator("#assignStudentListTable", {
            ajaxURL: route('assign.student.list'),
            ajaxParams: { form_data : form_data},
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
                    title: "#SL",
                    field: "sl",
                    headerHozAlign: "left",
                },
                {
                    title: "Reg. No",
                    field: "std_registration_no",
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "cr_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Term",
                    field: "term",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Group",
                    field: "group",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Evening/Weekend",
                    field: "ev_wk",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Status",
                    field: "sts_name",
                    headerHozAlign: "left",
                },
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
        $("#tabulator-export-csv-LSD").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-LSD").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-LSD").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Students Details",
            });
        });

        $("#tabulator-export-html-LSD").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-LSD").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

var groupAssignStudentListTable = (function () {
    var _tableGen = function (termdeclarationid, courseid, groupids) {
        // Setup Tabulator

        let tableContent = new Tabulator("#assignStudentListTable", {
            ajaxURL: route('assign.group.student.list'),
            ajaxParams: { termdeclarationid : termdeclarationid, courseid : courseid, groupids : groupids},
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
                    title: "#SL",
                    field: "sl",
                    headerHozAlign: "left",
                },
                {
                    title: "Reg. No",
                    field: "std_registration_no",
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "cr_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Term",
                    field: "term",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Group",
                    field: "group",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Evening/Weekend",
                    field: "ev_wk",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Status",
                    field: "sts_name",
                    headerHozAlign: "left",
                },
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
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    $('.copySelectedStudents').fadeIn();
                }else{
                    $('.copySelectedStudents').fadeOut();
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
        init: function (termdeclarationid, courseid, groupids) {
            _tableGen(termdeclarationid, courseid, groupids);
        },
    };
})();

var assignDeassignStudentListTable = (function () {
    var _tableGen = function (termdeclarationid, courseid, groupids) {
        // Setup Tabulator

        let tableContent = new Tabulator("#assignDeassignStudentListTable", {
            ajaxURL: route('assign.group.student.list'),
            ajaxParams: { termdeclarationid : termdeclarationid, courseid : courseid, groupids : groupids},
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
                    title: "#SL",
                    field: "sl",
                    headerHozAlign: "left",
                },
                {
                    title: "Reg. No",
                    field: "std_registration_no",
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "cr_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Term",
                    field: "term",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Group",
                    field: "group",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Evening/Weekend",
                    field: "ev_wk",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Status",
                    field: "sts_name",
                    headerHozAlign: "left",
                },
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
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    //$('.copySelectedStudents').fadeIn();
                }else{
                    //$('.copySelectedStudents').fadeOut();
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
        init: function (termdeclarationid, courseid, groupids) {
            _tableGen(termdeclarationid, courseid, groupids);
        },
    };
})();

var allGroupsStudentCountTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        var termdeclarationid = $('#allGroupsStudentCountTable').attr('data-termdeclarationid');
        var courseid = $('#allGroupsStudentCountTable').attr('data-courseid');
        var groups = $('#allGroupsStudentCountTable').attr('data-groups');

        let tableContent = new Tabulator("#allGroupsStudentCountTable", {
            ajaxURL: route('assign.group.count.list'),
            ajaxParams: { termdeclarationid : termdeclarationid, courseid : courseid, groups : groups},
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
                    title: "Group",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Students",
                    field: "count",
                    headerHozAlign: "left",
                    headerSort: false,
                },
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
            rowSelectionChanged:function(data, rows){
                var ids = [];
                //console.log(data);
                //console.log(rows);
                if(rows.length > 0){
                    var termdeclarationid = []; // data[0].termdeclarationid;
                    var courseid = []; //data[0].courseid;
                    var groupids = []; //data[0].groupids;

                    $.each(data, function(index, row) {
                        termdeclarationid.push(row.termdeclarationid)
                        courseid.push(row.courseid)
                        var rowGroups = row.groupids.split(',');
                        if(rowGroups.length > 0){
                            for(let g = 0; g < rowGroups.length; g++){
                                groupids.push(rowGroups[g]);
                            }
                        }
                    });

                    console.log(termdeclarationid)
                    console.log(courseid)
                    console.log(groupids)

                    $('.assignStudentListTableWrap').fadeIn('fast', function(){
                        groupAssignStudentListTable.init(termdeclarationid, courseid, groupids);
                    })
                }else{
                    $('.assignStudentListTableWrap').fadeOut('fast', function(){
                        $('#assignStudentListTable').removeAttr('tabulator-layout').removeAttr('role').removeClass('tabulator').html('');
                    })
                }
            },
            selectableCheck:function(row){
                return row.getData().count > 0; //allow selection of rows where the age is greater than 18
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

var allADGroupsStudentCountTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        var termdeclarationid = $('#allADGroupsStudentCountTable').attr('data-termdeclarationid');
        var courseid = $('#allADGroupsStudentCountTable').attr('data-courseid');
        var groups = $('#allADGroupsStudentCountTable').attr('data-groups');

        let tableContent = new Tabulator("#allADGroupsStudentCountTable", {
            ajaxURL: route('assign.deassign.group.count.list'),
            ajaxParams: { termdeclarationid : termdeclarationid, courseid : courseid, groups : groups},
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
                    title: "Group",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Students",
                    field: "count",
                    headerHozAlign: "left",
                    headerSort: false,
                },
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
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    var termdeclarationid = [];
                    var courseid = [];
                    var groupids = [];

                    $.each(data, function(index, row) {
                        termdeclarationid.push(row.termdeclarationid)
                        courseid.push(row.courseid)
                        var rowGroups = row.groupids.split(',');
                        if(rowGroups.length > 0){
                            for(let g = 0; g < rowGroups.length; g++){
                                groupids.push(rowGroups[g]);
                            }
                        }
                    });

                    //console.log(termdeclarationid)
                    //console.log(courseid)
                    //console.log(groupids)

                    $('.assignDeassignStudentListTableWrap').fadeIn('fast', function(){
                        assignDeassignStudentListTable.init(termdeclarationid, courseid, groupids);
                    })
                }else{
                    $('.assignDeassignStudentListTableWrap').fadeOut('fast', function(){
                        $('#assignDeassignStudentListTable').removeAttr('tabulator-layout').removeAttr('role').removeClass('tabulator').html('');
                    })
                }
            },
            selectableCheck:function(row){
                return row.getData().count > 0; //allow selection of rows where the age is greater than 18
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
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    if($('#studentSearchForm').length > 0){
        let tomOptionsMul = {
            ...tomOptions,
            plugins: {
                ...tomOptions.plugins,
                remove_button: {
                    title: "Remove this item",
                },
            }
        };
        var student_status = new TomSelect('#student_status', tomOptionsMul);
        var academic_year = new TomSelect('#academic_year', tomOptionsMul);
        var intake_semester = new TomSelect('#intake_semester', tomOptionsMul);
        var attendance_semester = new TomSelect('#attendance_semester', tomOptionsMul);
        var course = new TomSelect('#course', tomOptionsMul);
        var group = new TomSelect('#group', tomOptionsMul);
        var module = new TomSelect('#module', tomOptionsMul);
        var term_status = new TomSelect('#term_status', tomOptionsMul);
        var student_type = new TomSelect('#student_type', tomOptionsMul);
        var group_student_status = new TomSelect('#group_student_status', tomOptionsMul);

        /*Autocomplete Student Reg No Start*/
        $('.registration_no').on('keyup', function(){
            var $theInput = $(this);
            var SearchVal = $theInput.val();

            if(SearchVal.length >= 3){
                axios({
                    method: "post",
                    url: route('student.filter.id'),
                    data: {SearchVal : SearchVal},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $theInput.siblings('.autoFillDropdown').html(response.data.htm).fadeIn();
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                        $theInput.siblings('.autoFillDropdown').html('').fadeOut();
                    }
                });
            }else{
                $theInput.siblings('.autoFillDropdown').html('').fadeOut();
            }
        });

        $('.autoFillDropdown').on('click', 'li a:not(".disable")', function(e){
            e.preventDefault();
            var registration_no = $(this).attr('href');
            $(this).parent('li').parent('ul.autoFillDropdown').siblings('.registration_no').val(registration_no);
            $(this).parent('li').parent('.autoFillDropdown').html('').fadeOut();
        });
        /*Autocomplete Student Reg No End*/

        /* Course Module Dependencies Start*/
        $('#course').on('change', function(){
            var $theCourse = $(this);
            var theCourse = ($theCourse.val() != '' && $theCourse.val() > 0 ? $theCourse.val() : []);
            module.clear(true);
            module.disable();
            $theCourse.siblings('label').children('.theLoading').fadeIn();

            axios({
                method: "post",
                url: route('assign.module.list'),
                data: {theCourse : theCourse},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theCourse.siblings('label').children('.theLoading').fadeOut();
                if (response.status == 200) {
                    module.enable();
                    module.clearOptions();
                    $.each(response.data.res, function(index, row) {
                        module.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    module.refreshOptions()
                }
            }).catch(error => {
                if (error.response) {
                    $theCourse.siblings('label').children('.theLoading').fadeOut();
                    console.log('error');
                }
            });
        })
        /* Course Module Dependencies End*/

        /* Reset Search Form Start */
        function resetStudentIDSearch(){
            $('#registration_no').val('');
        }

        function resetStudentSearch(){
            student_status.clear(true);
            $('#studentSearchStatus').val('0');
            $('#student_id, #student_name, #student_dob #student_abr, #student_ssn, #student_uhn, #student_mobile, #student_email, #student_post_code').val('');
        }

        function resetGroupSearch(){
            academic_year.clear(true);
            intake_semester.clear(true);
            attendance_semester.clear(true);
            course.clear(true);
            group.clear(true);
            module.clear(true);
            term_status.clear(true);
            student_type.clear(true);
            group_student_status.clear(true);
            $('#evening_weekend').val('');
            $('#groupSearchStatus').val('0');
        }
        /* Reset Search Form END */

        const studentSearchAccordion = tailwind.Accordion.getOrCreateInstance(document.querySelector("#studentSearchAccordion"));
        $('#advanceSearchToggle').on('click', function(e){
            e.preventDefault();
            $('#studentSearchAccordionWrap').slideToggle();
            $('#studentIDSearchBtn').fadeToggle();
            studentSearchAccordion.toggle();
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            resetTreeView();
            resetStudentListTable();
        });

        $('#studentSearchBtn').on('click', function(){
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            if($(this).hasClass('collapsed')){
                $('#studentSearchStatus').val(1);
                $('#groupSearchStatus').val(0);
            }else{
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(0);
            }

            resetTreeView();
            resetStudentListTable();
        });

        $('#studentGroupSearchBtn').on('click', function(){
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            if($(this).hasClass('collapsed')){
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(1);
            }else{
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(0);
            }

            resetTreeView();
            resetStudentListTable();
        });

        $('#assignedStudentTermSearchBtn').on('click', function(){
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            resetTreeView();
            resetStudentListTable();
            $('#studentSearchStatus').val(0);
            $('#groupSearchStatus').val(0);
        });


        /* Start List Table Inits */
        function filterStudentListTable() {
            $('.assignStudentListTableWrap').fadeIn('fast', function(){
                assignStudentListTable.init();
            })
        }

        function resetStudentListTable() {
            $('.assignStudentListTableWrap').fadeOut('fast', function(){
                $('#assignStudentListTable').removeAttr('tabulator-layout').removeAttr('role').removeClass('tabulator').html('');
            })
        }

        $("#studentIDSearchBtn, #studentSearchBtnSubmit, #studentGroupSearchBtnSubmit").on("click", function (event) {
            event.preventDefault();
            filterStudentListTable();
        });

        $("#resetStudentSearch").on("click", function (event) {
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            resetTreeView();
            resetStudentListTable();
        });

        function resetADStudentListTable() {
            $('.assignDeassignStudentListTableWrap').fadeOut('fast', function(){
                $('#assignDeassignStudentListTable').removeAttr('tabulator-layout').removeAttr('role').removeClass('tabulator').html('');
            })
        }
        /* End List Table Inits */

        /*Start Tree Search Start*/
        var tree_term_declaration = new TomSelect('#tree_term_declaration', tomOptions);
        var tree_course = new TomSelect('#tree_course', tomOptions);
            tree_course.disable();
        var tree_group = new TomSelect('#tree_group', tomOptions);
            tree_group.disable();
        var tree_module = new TomSelect('#tree_module', tomOptions);
            tree_module.disable();

        function resetTreeView(){
            tree_term_declaration.clear(true);
            $('.theTreeCourseWrap').fadeOut('fast', function(){
                tree_course.clear(true);
                tree_course.clearOptions();
                tree_course.disable();
            })
            $('.theTreeGroupWrap').fadeOut('fast', function(){
                tree_group.clear(true);
                tree_group.clearOptions();
                tree_group.disable();
            })
            $('.theTreeModuleWrap').fadeOut('fast', function(){
                tree_module.clear(true);
                tree_module.clearOptions();
                tree_module.disable();
            })
            $('.theTreeSubmitWrap').fadeOut('fast');
            $('.treeViewWrap').fadeOut('fast').html('');
        }

        $('#tree_term_declaration').on('change', function(){
            var $theTreeTerm = $(this);
            var theTreeTerm = $theTreeTerm.val();

            resetStudentListTable();

            $('.theTreeSubmitWrap').fadeOut('fast');
            $('.treeViewWrap').fadeOut('fast').html('');
            $('.theTreeCourseWrap').fadeOut('fast', function(){
                tree_course.clear(true);
                tree_course.clearOptions();
                tree_course.disable();
            })
            $('.theTreeGroupWrap').fadeOut('fast', function(){
                tree_group.clear(true);
                tree_group.clearOptions();
                tree_group.disable();
            })
            $('.theTreeModuleWrap').fadeOut('fast', function(){
                tree_module.clear(true);
                tree_module.clearOptions();
                tree_module.disable();
            })

            if(theTreeTerm > 0){
                $theTreeTerm.siblings('label').children('.theLoading').fadeIn();
                $('.theTreeSubmitWrap').fadeIn('fast');
                axios({
                    method: "post",
                    url: route('assign.tree.get.course.list'),
                    data: {termdeclarationid : theTreeTerm},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    $theTreeTerm.siblings('label').children('.theLoading').fadeOut();
                    if (response.status == 200) {
                        $('.theTreeCourseWrap').fadeIn('fast', function(){
                            tree_course.enable();
                        });
                        $.each(response.data.res, function(index, row) {
                            tree_course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        tree_course.refreshOptions()
                    }
                }).catch(error => {
                    if (error.response) {
                        $('svg.theLoading').fadeOut();
                        console.log('error');
                    }
                });
            }else{
                $('svg.theLoading').fadeOut();
                $('.theTreeSubmitWrap').fadeOut('fast');
                $('.treeViewWrap').fadeOut('fast').html('');
                $('.theTreeCourseWrap').fadeOut('fast', function(){
                    tree_course.clear(true);
                    tree_course.clearOptions();
                    tree_course.disable();
                });
                $('.theTreeGroupWrap').fadeOut('fast', function(){
                    tree_group.clear(true);
                    tree_group.clearOptions();
                    tree_group.disable();
                });
                $('.theTreeModuleWrap').fadeOut('fast', function(){
                    tree_module.clear(true);
                    tree_module.clearOptions();
                    tree_module.disable();
                })
            }
        });

        $('#tree_course').on('change', function(){
            var $theTreeCourse = $(this);
            var theTreeCourse = $theTreeCourse.val();
            var $theTreeTerm = $('#tree_term_declaration');
            var theTreeTerm = $theTreeTerm.val();
            
            resetStudentListTable();
            
            $('.treeViewWrap').fadeOut('fast').html('');
            $('.theTreeGroupWrap').fadeOut('fast', function(){
                tree_group.clear(true);
                tree_group.clearOptions();
                tree_group.disable();
            })
            $('.theTreeModuleWrap').fadeOut('fast', function(){
                tree_module.clear(true);
                tree_module.clearOptions();
                tree_module.disable();
            })

            if(theTreeCourse > 0){
                $theTreeCourse.siblings('label').children('.theLoading').fadeIn();
                axios({
                    method: "post",
                    url: route('assign.tree.get.group.module.list'),
                    data: {termdeclarationid : theTreeTerm, courseid : theTreeCourse},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    $theTreeCourse.siblings('label').children('.theLoading').fadeOut();
                    if (response.status == 200) {
                        $('.theTreeGroupWrap').fadeIn('fast', function(){
                            tree_group.enable();
                            if(typeof response.data.res.groups !== 'undefined'){
                                $.each(response.data.res.groups, function(index, row) {
                                    tree_group.addOption({
                                        value: row.id,
                                        text: row.name,
                                    });
                                });
                            }
                            tree_group.refreshOptions()
                        });
                        $('.theTreeModuleWrap').fadeIn('fast', function(){
                            tree_module.enable();
                            if(typeof response.data.res.modules !== 'undefined'){
                                $.each(response.data.res.modules, function(index, row) {
                                    tree_module.addOption({
                                        value: row.id,
                                        text: row.name,
                                    });
                                });
                            }
                            tree_module.refreshOptions()
                        });
                    }
                }).catch(error => {
                    if (error.response) {
                        $('svg.theLoading').fadeOut();
                        console.log('error');
                    }
                });
            }else{
                $('svg.theLoading').fadeOut();
                $('.treeViewWrap').fadeOut('fast').html('');
                $('.theTreeGroupWrap').fadeOut('fast', function(){
                    tree_group.clear(true);
                    tree_group.clearOptions();
                    tree_group.disable();
                });
                $('.theTreeModuleWrap').fadeOut('fast', function(){
                    tree_module.clear(true);
                    tree_module.clearOptions();
                    tree_module.disable();
                })
            }
        });

        $('#assignedStudentTermSubmitBtn').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var $theTreeTerm = $('#tree_term_declaration');
            var $theTreeCourse = $('#tree_course');
            var $theTreeGroup = $('#tree_group');
            var $theTreeModule = $('#tree_module');

            var theTreeTerm = $theTreeTerm.val();
            var theTreeCourse = ($theTreeCourse.val() != '' && $theTreeCourse.val() > 0 ? $theTreeCourse.val() : 0);
            var theTreeGroup = ($theTreeGroup.val() != '' && $theTreeGroup.val() > 0 ? $theTreeGroup.val() : 0);
            var theTreeModule = ($theTreeModule.val() != '' && $theTreeModule.val() > 0 ? $theTreeModule.val() : 0);

            $theBtn.attr('disabled', 'disabled');
            resetStudentListTable();

            axios({
                method: "post",
                url: route('assign.get.tree'),
                data: {theTreeTerm : theTreeTerm, theTreeCourse : theTreeCourse, theTreeGroup : theTreeGroup, theTreeModule : theTreeModule},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theBtn.removeAttr('disabled');
                if (response.status == 200) {
                    $('.treeViewWrap').fadeIn('fast').html(response.data.htm);

                    tailwind.svgLoader();
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });

                    if(theTreeGroup > 0){
                        allGroupsStudentCountTable.init();
                    }
                }
            }).catch(error => {
                if (error.response) {
                    $theBtn.removeAttr('disabled');
                    $('.treeViewWrap').fadeOut('fast').html('');
                    console.log('error');
                }
            });
        })

        $('.treeViewWrap').on('click', '.searchTreeWrap .theTermDeclaraton', function(e){
            e.preventDefault();
            var $link = $(this);
            var $parent = $link.parent('li');

            if($parent.hasClass('hasData')){
                $('> .theChild', $parent).slideToggle();
                $parent.toggleClass('opened');
            }else{
                $('svg', $link).fadeIn();
                var termdeclarationid = $link.attr('data-termdeclarationid');
                axios({
                    method: "post",
                    url: route('assign.tree.get.courses'),
                    data: {termdeclarationid : termdeclarationid},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    $('svg', $link).fadeOut();
                    if (response.status == 200) {
                        $parent.addClass('hasData opened');
                        $parent.append(response.data.htm);

                        $('.treeViewWrap .classPlanTreeResultWrap').fadeOut('fast', function(){
                            $('.treeViewWrap .classPlanTreeResultWrap').html('');
                            $('.treeViewWrap .classPlanTreeResultNotice').fadeIn('fast', function(){
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

        $('.treeViewWrap').on('click', '.classPlanTree .theCourse', function(e){
            e.preventDefault();
            var $link = $(this);
            var $parent = $link.parent('li');
            var courseid = $link.attr('data-courseid');
            var termdeclarationid = $link.attr('data-termdeclarationid');
    
            
            if($parent.hasClass('hasData')){
                $('> .theChild', $parent).slideToggle();
                if(!$parent.hasClass('opened')){
                    var groupIds = [];
                    var $theGroupUl = $parent.find('.theChild');
                    $theGroupUl.find('a.theGroup').each(function(){
                        console.log($(this).attr('data-groupid'));
                        groupIds.push($(this).attr('data-groupid'));
                    });
                    if(groupIds.length > 0){
                        var theTable = '';
                        theTable += '<div class="overflow-x-auto scrollbar-hidden">';
                            theTable += '<div id="allGroupsStudentCountTable" data-termdeclarationid="'+termdeclarationid+'" data-courseid="'+courseid+'" data-groups="'+groupIds.join(',')+'" class="mt-5 table-report table-report--tabulator"></div>';
                        theTable += '</div>';
                        $('.treeViewWrap .classPlanTreeResultNotice').fadeOut('fast', function(){
                            $('.treeViewWrap .classPlanTreeResultWrap').fadeIn('fast', function(){
                                $('.treeViewWrap .classPlanTreeResultWrap').html(theTable);
                                
                                if($('.treeViewWrap #allGroupsStudentCountTable').length > 0){
                                    allGroupsStudentCountTable.init();
                                }
                                createIcons({
                                    icons,
                                    "stroke-width": 1.5,
                                    nameAttr: "data-lucide",
                                });
                            })
                        });
                    }
                    $parent.addClass('opened');
                }else{
                    var $theGroupUl = $parent.find('.theChild');
                    $theGroupUl.find('li.opened').removeClass('opened');
                    $parent.removeClass('opened');
                    $('.treeViewWrap .classPlanTreeResultWrap').fadeOut('fast', function(){
                        $('.treeViewWrap .classPlanTreeResultWrap').html('');
                        $('.treeViewWrap .classPlanTreeResultNotice').fadeIn('fast', function(){
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                        })
                    });
                }
            }else{
                $('svg', $link).fadeIn();
                axios({
                    method: "post",
                    url: route('assign.tree.get.groups'),
                    data: {courseid : courseid, termdeclarationid : termdeclarationid},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('svg', $link).fadeOut();
                        $parent.addClass('hasData opened');
                        $parent.append(response.data.htm);
                        
                        if(response.data.suc == 1){
                            $('.treeViewWrap .classPlanTreeResultNotice').fadeOut('fast', function(){
                                $('.treeViewWrap .classPlanTreeResultWrap').fadeIn('fast', function(){
                                    $('.treeViewWrap .classPlanTreeResultWrap').html(response.data.table);
                                    
                                    if($('.treeViewWrap #allGroupsStudentCountTable').length > 0){
                                        allGroupsStudentCountTable.init();
                                    }
                                    createIcons({
                                        icons,
                                        "stroke-width": 1.5,
                                        nameAttr: "data-lucide",
                                    });
                                })
                            });
                        }else{
                            $('.treeViewWrap .classPlanTreeResultWrap').fadeOut('fast', function(){
                                $('.treeViewWrap .treeViewWrap .classPlanTreeResultWrap').html('');
                                $('.treeViewWrap .classPlanTreeResultNotice').fadeIn('fast', function(){
                                    createIcons({
                                        icons,
                                        "stroke-width": 1.5,
                                        nameAttr: "data-lucide",
                                    });
                                })
                            });
                        }
    
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

        $('.treeViewWrap').on('click', '.classPlanTree .theGroup', function(e){
            e.preventDefault();
            var $link = $(this);
            var $parent = $link.parent('li');

            var termdeclarationid = $link.attr('data-termdeclarationid');
            var courseid = $link.attr('data-courseid');
            var groupid = $link.attr('data-groupid');

            if($parent.hasClass('hasData')){
                $('> .theChild', $parent).slideToggle();
            }

            if(!$parent.hasClass('opened')){
                $parent.siblings('li').removeClass('opened')
                $parent.addClass('opened');

                var theTable = '';
                theTable += '<div class="overflow-x-auto scrollbar-hidden">';
                    theTable += '<div id="allGroupsStudentCountTable" data-termdeclarationid="'+termdeclarationid+'" data-courseid="'+courseid+'" data-groups="'+groupid+'" class="mt-5 table-report table-report--tabulator"></div>';
                theTable += '</div>';
                $('.treeViewWrap .classPlanTreeResultNotice').fadeOut('fast', function(){
                    $('.treeViewWrap .classPlanTreeResultWrap').fadeIn('fast', function(){
                        $('.treeViewWrap .classPlanTreeResultWrap').html(theTable);
                        
                        if($('.treeViewWrap #allGroupsStudentCountTable').length > 0){
                            allGroupsStudentCountTable.init();
                        }
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    })
                });
            }else{
                $parent.removeClass('opened');

                $('.treeViewWrap .classPlanTreeResultWrap').fadeOut('fast', function(){
                    $('.treeViewWrap .classPlanTreeResultWrap').html('');
                    $('.treeViewWrap .classPlanTreeResultNotice').fadeIn('fast', function(){
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    })
                });
            }
        });
        /*End Tree Search Start*/


        /*Assign/Deassign Tree Search Start*/
        var ad_tree_term_declaration = new TomSelect('#ad_tree_term_declaration', tomOptions);
        var ad_tree_course = new TomSelect('#ad_tree_course', tomOptions);
            ad_tree_course.disable();
        var ad_tree_group = new TomSelect('#ad_tree_group', tomOptions);
            ad_tree_group.disable();

        $('#ad_tree_term_declaration').on('change', function(){
            var $theTreeTerm = $(this);
            var theTreeTerm = $theTreeTerm.val();

            resetADStudentListTable();

            $('.theADTreeSubmitWrap').fadeOut('fast');
            $('.assignDeassignTreeViewWrap').fadeOut('fast').html('');
            $('.theADTreeCourseWrap').fadeOut('fast', function(){
                ad_tree_course.clear(true);
                ad_tree_course.clearOptions();
                ad_tree_course.disable();
            })
            $('.theADTreeGroupWrap').fadeOut('fast', function(){
                ad_tree_group.clear(true);
                ad_tree_group.clearOptions();
                ad_tree_group.disable();
            })

            if(theTreeTerm > 0){
                $theTreeTerm.siblings('label').children('.theLoading').fadeIn();
                $('.theADTreeSubmitWrap').fadeIn('fast');
                axios({
                    method: "post",
                    url: route('assign.tree.get.course.list'),
                    data: {termdeclarationid : theTreeTerm},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    $theTreeTerm.siblings('label').children('.theLoading').fadeOut();
                    if (response.status == 200) {
                        $('.theADTreeCourseWrap').fadeIn('fast', function(){
                            ad_tree_course.enable();
                        });
                        $.each(response.data.res, function(index, row) {
                            ad_tree_course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        ad_tree_course.refreshOptions()
                    }
                }).catch(error => {
                    if (error.response) {
                        $('svg.theLoading').fadeOut();
                        console.log('error');
                    }
                });
            }else{
                $('.theADTreeSubmitWrap').fadeOut('fast');
                $('.assignDeassignTreeViewWrap').fadeOut('fast').html('');
                $('.theADTreeCourseWrap').fadeOut('fast', function(){
                    ad_tree_course.clear(true);
                    ad_tree_course.clearOptions();
                    ad_tree_course.disable();
                })
                $('.theADTreeGroupWrap').fadeOut('fast', function(){
                    ad_tree_group.clear(true);
                    ad_tree_group.clearOptions();
                    ad_tree_group.disable();
                })
            }
        });

        $('#ad_tree_course').on('change', function(){
            var $theTreeCourse = $(this);
            var theTreeCourse = $theTreeCourse.val();
            var $theTreeTerm = $('#ad_tree_term_declaration');
            var theTreeTerm = $theTreeTerm.val();
            
            resetADStudentListTable();
            
            $('.assignDeassignTreeViewWrap').fadeOut('fast').html('');
            $('.theADTreeGroupWrap').fadeOut('fast', function(){
                ad_tree_group.clear(true);
                ad_tree_group.clearOptions();
                ad_tree_group.disable();
            })

            if(theTreeCourse > 0){
                $theTreeCourse.siblings('label').children('.theLoading').fadeIn();
                axios({
                    method: "post",
                    url: route('assign.tree.get.group.module.list'),
                    data: {termdeclarationid : theTreeTerm, courseid : theTreeCourse},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    $theTreeCourse.siblings('label').children('.theLoading').fadeOut();
                    if (response.status == 200) {
                        $('.theADTreeGroupWrap').fadeIn('fast', function(){
                            ad_tree_group.enable();
                        });
                        $.each(response.data.res, function(index, row) {
                            ad_tree_group.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        ad_tree_group.refreshOptions()
                    }
                }).catch(error => {
                    if (error.response) {
                        $('svg.theLoading').fadeOut();
                        console.log('error');
                    }
                });
            }else{
                $('svg.theLoading').fadeOut();
                $('.assignDeassignTreeViewWrap').fadeOut('fast').html('');
                $('.theADTreeGroupWrap').fadeOut('fast', function(){
                    ad_tree_group.clear(true);
                    ad_tree_group.clearOptions();
                    ad_tree_group.disable();
                })
            }
        });

        $('#assignDeassignStudentTermSubmitBtn').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var $theTreeTerm = $('#ad_tree_term_declaration');
            var $theTreeCourse = $('#ad_tree_course');
            var $theTreeGroup = $('#ad_tree_group');

            var theTreeTerm = $theTreeTerm.val();
            var theTreeCourse = ($theTreeCourse.val() != '' && $theTreeCourse.val() > 0 ? $theTreeCourse.val() : 0);
            var theTreeGroup = ($theTreeGroup.val() != '' && $theTreeGroup.val() > 0 ? $theTreeGroup.val() : 0);

            $theBtn.attr('disabled', 'disabled');
            resetADStudentListTable();

            axios({
                method: "post",
                url: route('assign.get.tree'),
                data: {theTreeTerm : theTreeTerm, theTreeCourse : theTreeCourse, theTreeGroup : theTreeGroup, assignDeassign : 1},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theBtn.removeAttr('disabled');
                if (response.status == 200) {
                    $('.assignDeassignTreeViewWrap').fadeIn('fast').html(response.data.htm);

                    tailwind.svgLoader();
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });

                    if(theTreeGroup > 0){
                        allADGroupsStudentCountTable.init();
                    }
                }
            }).catch(error => {
                if (error.response) {
                    $theBtn.removeAttr('disabled');
                    $('.assignDeassignTreeViewWrap').fadeOut('fast').html('');
                    console.log('error');
                }
            });
        });

        $('.assignDeassignTreeViewWrap').on('click', '.searchTreeWrap .theTermDeclaraton', function(e){
            e.preventDefault();
            var $link = $(this);
            var $parent = $link.parent('li');

            if($parent.hasClass('hasData')){
                $('> .theChild', $parent).slideToggle();
                $parent.toggleClass('opened');
            }else{
                $('svg', $link).fadeIn();
                var termdeclarationid = $link.attr('data-termdeclarationid');
                axios({
                    method: "post",
                    url: route('assign.tree.get.courses'),
                    data: {termdeclarationid : termdeclarationid},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    $('svg', $link).fadeOut();
                    if (response.status == 200) {
                        $parent.addClass('hasData opened');
                        $parent.append(response.data.htm);

                        $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').fadeOut('fast', function(){
                            $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').html('');
                            $('.assignDeassignTreeViewWrap .classPlanTreeResultNotice').fadeIn('fast', function(){
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

        $('.assignDeassignTreeViewWrap').on('click', '.classPlanTree .theCourse', function(e){
            e.preventDefault();
            var $link = $(this);
            var $parent = $link.parent('li');
            var courseid = $link.attr('data-courseid');
            var termdeclarationid = $link.attr('data-termdeclarationid');
    
            
            if($parent.hasClass('hasData')){
                $('> .theChild', $parent).slideToggle();
                if(!$parent.hasClass('opened')){
                    var groupIds = [];
                    var $theGroupUl = $parent.find('.theChild');
                    $theGroupUl.find('a.theGroup').each(function(){
                        groupIds.push($(this).attr('data-groupid'));
                    });
                    if(groupIds.length > 0){
                        var theTable = '';
                        theTable += '<div class="overflow-x-auto scrollbar-hidden">';
                            theTable += '<div id="allADGroupsStudentCountTable" data-termdeclarationid="'+termdeclarationid+'" data-courseid="'+courseid+'" data-groups="'+groupIds.join(',')+'" class="mt-5 table-report table-report--tabulator"></div>';
                        theTable += '</div>';
                        $('.assignDeassignTreeViewWrap .classPlanTreeResultNotice').fadeOut('fast', function(){
                            $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').fadeIn('fast', function(){
                                $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').html(theTable);
                                
                                if($('.assignDeassignTreeViewWrap #allADGroupsStudentCountTable').length > 0){
                                    allADGroupsStudentCountTable.init();
                                }
                                createIcons({
                                    icons,
                                    "stroke-width": 1.5,
                                    nameAttr: "data-lucide",
                                });
                            })
                        });
                    }
                    $parent.addClass('opened');
                }else{
                    var $theGroupUl = $parent.find('.theChild');
                    $theGroupUl.find('li.opened').removeClass('opened');
                    $parent.removeClass('opened');
                    $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').fadeOut('fast', function(){
                        $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').html('');
                        $('.assignDeassignTreeViewWrap .classPlanTreeResultNotice').fadeIn('fast', function(){
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                        })
                    });
                }
            }else{
                $('svg', $link).fadeIn();
                axios({
                    method: "post",
                    url: route('assign.tree.get.groups'),
                    data: {courseid : courseid, termdeclarationid : termdeclarationid, assignDeassign : 1},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('svg', $link).fadeOut();
                        $parent.addClass('hasData opened');
                        $parent.append(response.data.htm);
                        
                        if(response.data.suc == 1){
                            $('.assignDeassignTreeViewWrap .classPlanTreeResultNotice').fadeOut('fast', function(){
                                $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').fadeIn('fast', function(){
                                    $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').html(response.data.table);
                                    
                                    if($('.assignDeassignTreeViewWrap #allADGroupsStudentCountTable').length > 0){
                                        allADGroupsStudentCountTable.init();
                                    }
                                    createIcons({
                                        icons,
                                        "stroke-width": 1.5,
                                        nameAttr: "data-lucide",
                                    });
                                })
                            });
                        }else{
                            $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').fadeOut('fast', function(){
                                $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').html('');
                                $('.assignDeassignTreeViewWrap .classPlanTreeResultNotice').fadeIn('fast', function(){
                                    createIcons({
                                        icons,
                                        "stroke-width": 1.5,
                                        nameAttr: "data-lucide",
                                    });
                                })
                            });
                        }
    
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

        $('.assignDeassignTreeViewWrap').on('click', '.classPlanTree .theGroup', function(e){
            e.preventDefault();
            var $link = $(this);
            var $parent = $link.parent('li');

            var termdeclarationid = $link.attr('data-termdeclarationid');
            var courseid = $link.attr('data-courseid');
            var groupid = $link.attr('data-groupid');

            if($parent.hasClass('hasData')){
                $('> .theChild', $parent).slideToggle();
            }

            if(!$parent.hasClass('opened')){
                $parent.siblings('li').removeClass('opened')
                $parent.addClass('opened');

                var theTable = '';
                theTable += '<div class="overflow-x-auto scrollbar-hidden">';
                    theTable += '<div id="allADGroupsStudentCountTable" data-termdeclarationid="'+termdeclarationid+'" data-courseid="'+courseid+'" data-groups="'+groupid+'" class="mt-5 table-report table-report--tabulator"></div>';
                theTable += '</div>';
                $('.assignDeassignTreeViewWrap .classPlanTreeResultNotice').fadeOut('fast', function(){
                    $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').fadeIn('fast', function(){
                        $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').html(theTable);
                        
                        if($('.assignDeassignTreeViewWrap #allADGroupsStudentCountTable').length > 0){
                            allADGroupsStudentCountTable.init();
                        }
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    })
                });
            }else{
                $parent.removeClass('opened');

                $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').fadeOut('fast', function(){
                    $('.assignDeassignTreeViewWrap .classPlanTreeResultWrap').html('');
                    $('.assignDeassignTreeViewWrap .classPlanTreeResultNotice').fadeIn('fast', function(){
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    })
                });
            }
        });
        /*Assign/Deassign Tree Search End*/
    }
})();