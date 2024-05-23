import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import IMask from 'imask';
import TomSelect from "tom-select";



("use strict");
var instancetermListtable = (function () {
    var _tableGen = function (courseCreationInstanceID = 0) {
        // Setup Tabulator
        let tableID = 'instancetermTable_'+courseCreationInstanceID;

        let tableContent = new Tabulator("#"+tableID, {
            ajaxURL: route("instance.term.list"),
            ajaxParams: { creationinstanceid: courseCreationInstanceID },
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
                    title: "Term",
                    field: "term",
                    headerHozAlign: "left",
                },
                {
                    title: "Session",
                    field: "session_term",
                    headerHozAlign: "left",
                },
                {
                    title: "Start",
                    field: "start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "End",
                    field: "end_date",
                    headerHozAlign: "left",
                },
                {
                    title: "T. Weeks",
                    field: "total_teaching_weeks",
                    headerHozAlign: "left",
                },
                {
                    title: "T. Start",
                    field: "teaching_start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "T. End",
                    field: "teaching_end_date",
                    headerHozAlign: "left",
                },
                {
                    title: "R. Start",
                    field: "revision_start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "R. End",
                    field: "revision_end_date",
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
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#instancetermEditModal" type="button" class="editTermBtn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="deleteTermBtn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
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
        init: function (courseCreationInstanceID) {
            _tableGen(courseCreationInstanceID);
        },
    };
})();

var hideCollapsibleIcon = function(cell, formatterParams, onRendered){ 
    return '<span class="chellIconWrapper inline-flex">+</span>';
};

var courseCreationINListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-04").val() != "" ? $("#query-04").val() : "";
        let status = $("#status-04").val() != "" ? $("#status-04").val() : "";
        let creationid = $("#courseCreationInstTable").attr('data-creationid') != "" ? $("#courseCreationInstTable").attr('data-creationid') : "0";

        let tableContent = new Tabulator("#courseCreationInstTable", {
            ajaxURL: route("course.creation.instance.list"),
            ajaxParams: { querystr: querystr, status: status, creationid: creationid},
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

            //dataTree: true,
            //selectable: true,
            height:"auto",

            columns: [
                {
                    formatter: hideCollapsibleIcon, 
                    align: "left", 
                    title: "&nbsp;", 
                    width: "90",
                    headerSort: false, 
                    download: false,
                    cellClick:function(e, row, formatterParams){
                        const courseCreationInstanceId = row.getData().id;
                        let holderWrapEl = document.getElementById('subTableWrap_'+courseCreationInstanceId);
                        let termTableID = 'instancetermTable_'+courseCreationInstanceId;

                        if(row.getElement().classList.contains('active')){
                            row.getElement().classList.remove('active');
                            row.getElement().querySelector('.chellIconWrapper').innerHTML = '+';
                            holderWrapEl.style.display = 'none';
                        }else{
                            row.getElement().classList.add('active');
                            row.getElement().querySelector('.chellIconWrapper').innerHTML = '-';
                            holderWrapEl.style.display = 'block';
                            holderWrapEl.style.width = '100%';

                            if($('#'+termTableID).length > 0){
                                instancetermListtable.init(courseCreationInstanceId)
                            }
                        }     
                    }
                },
                {
                    title: "Academic Year",
                    field: "academic_year",
                    headerHozAlign: "left",
                },
                {
                    title: "Start Date",
                    field: "start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "End Date",
                    field: "end_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Teaching Week",
                    field: "total_teaching_week",
                    headerHozAlign: "left",
                },
                {
                    title: "Fees",
                    field: "fees",
                    headerHozAlign: "left",
                },
                {
                    title: "Reg. Fees",
                    field: "reg_fees",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    download: false,
                    width: "230",
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-academic_year_id="' +cell.getData().academic_year_id +'" data-tw-toggle="modal"  data-tw-target="#instancetermAddModal" class="addInstanceTermBtn btn btn-linkedin text-white btn-rounded ml-1 p-0 px-4 w-auto h-9"><i data-lucide="plus" class="w-4 h-4"></i> Add Term</button>';
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editCourseCreationInstModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            if(cell.getData().has_terms < 1){
                                btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                            }
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
            },
            rowFormatter: function(row, e) {
                const courseCreationInstanceId = row.getData().id;
                const hasTerms = row.getData().has_terms;

                var holderEl = document.createElement("div");
                holderEl.setAttribute('class', "pt-3 px-5 pb-5 overflow-x-auto scrollbar-hidden subTableWrap_"+courseCreationInstanceId);
                holderEl.setAttribute('id', "subTableWrap_"+courseCreationInstanceId);
                holderEl.style.display = "none";
                holderEl.style.boxSizing = "border-box";
                //holderEl.style.borderTop = "1px solid #e5e7eb";


                if(hasTerms > 0){
                    var tableEl = document.createElement("div");
                    tableEl.setAttribute('class', "table-report table-report--tabulator subTable"+courseCreationInstanceId);
                    tableEl.setAttribute('id', "instancetermTable_"+courseCreationInstanceId);
                    tableEl.setAttribute('data-coursecreationinstanceid', courseCreationInstanceId);

                    holderEl.appendChild(tableEl);
                }else{
                    holderEl.innerHTML = '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> &nbsp;No terms found under this Instance.</div>';
                }

                row.getElement().appendChild(holderEl);
            }
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
                sheetName: "Course Creation Instances",
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

    if ($("#courseCreationInstTable").length) {
        // Init Table
        courseCreationINListTable.init();

        // Filter function
        function filterHTMLForm() {
            courseCreationINListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-04")[0].addEventListener(
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
        $("#tabulator-html-filter-go-04").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-04").on("click", function (event) {
            $("#query-04").val("");
            $("#status-04").val("1");
            filterHTMLForm();
        });


        $(".datepicker.ccin").each(function () {
            var maskOptions = {
                mask: '00-00-0000'
            };
            var mask = IMask(this, maskOptions);
        });


        const addCourseCreationInstModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCourseCreationInstModal"));
        const editCourseCreationInstModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCourseCreationInstModal"));
        const succModalCCIN = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModalCCIN = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalCCIN"));

        let confModalDelTitleCCIN = 'Are you sure?';
        let confModalDelDescriptionCCIN = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescriptionCCIN = 'Do you really want to re-store these records? Click agree to continue.';

        const addCourseCreationInstModallEl = document.getElementById('addCourseCreationInstModal')
        addCourseCreationInstModallEl.addEventListener('hide.tw.modal', function(event) {
            
            let Fees = $('#addCourseCreationInstModal .modal-body input[name="fees"]').val();
            let regFees = $('#addCourseCreationInstModal .modal-body input[name="reg_fees"]').val();

            $('#addCourseCreationInstModal .acc__input-error').html('');
            $('#addCourseCreationInstModal .modal-body input[type="text"]').val('');
            $('#addCourseCreationInstModal .modal-body input[type="number"]').val('');
            $('#addCourseCreationInstModal .modal-body  select').val('');

            $('#addCourseCreationInstModal .modal-body input[name="fees"]').val(Fees);
            $('#addCourseCreationInstModal .modal-body input[name="reg_fees"]').val(regFees);
        });
        
        const editCourseCreationInstModalEl = document.getElementById('editCourseCreationInstModal')
        editCourseCreationInstModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editCourseCreationInstModal .acc__input-error').html('');
            $('#editCourseCreationInstModal .modal-body  input[type="text"]').val('');
            $('#editCourseCreationInstModal .modal-body  input[type="number"]').val('');
            $('#editCourseCreationInstModal .modal-body  select').val('');

            $('#editCourseCreationInstModal input[name="id"]').val('0');
        });

        const confirmModalCCINEL = document.getElementById('confirmModalCCIN');
        confirmModalCCINEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalCCIN .agreeWithCCIN').attr('data-id', '0');
            $('#confirmModalCCIN .agreeWithCCIN').attr('data-action', 'none');
        });

        // Delete Course
        $('#courseCreationInstTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModalCCIN.show();
            document.getElementById('confirmModalCCIN').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalCCIN .confModTitleCCIN').html(confModalDelTitleCCIN);
                $('#confirmModalCCIN .confModDescCCIN').html('Do you really want to delete these record? If yes, then please click on agree btn.');
                $('#confirmModalCCIN .agreeWithCCIN').attr('data-id', rowID);
                $('#confirmModalCCIN .agreeWithCCIN').attr('data-action', 'DELETE');
            });
        });

        $('#courseCreationInstTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModalCCIN.show();
            document.getElementById('confirmModalCCIN').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalCCIN .confModTitleCCIN').html(confModalDelTitleCCIN);
                $('#confirmModalCCIN .confModDescCCIN').html('Do you really want to restore these record?  If yes, then please click on agree btn.');
                $('#confirmModalCCIN .agreeWithCCIN').attr('data-id', courseID);
                $('#confirmModalCCIN .agreeWithCCIN').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#confirmModalCCIN .agreeWithCCIN').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalCCIN button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('course.creation.instance.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalCCIN button').removeAttr('disabled');
                        confirmModalCCIN.hide();

                        succModalCCIN.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Congratulation!');
                            $('#successModal .successModalDesc').html('Course Creation Instance data successfully deleted.');
                        });
                    }
                    courseCreationINListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('course.creation.instance.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalCCIN button').removeAttr('disabled');
                        confirmModalCCIN.hide();

                        succModalCCIN.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Course Creation Data Successfully Restored!');
                        });
                    }
                    courseCreationINListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })


        $("#courseCreationInstTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("course.creation.instance.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    var cf = $('#editCourseCreationInstForm input[name="fees"]').attr('data-cf');
                    var fees = (dataset.fees ? dataset.fees : (cf != 'undefined' && cf != '' ? cf : ''));
                    var crf = $('#editCourseCreationInstForm input[name="reg_fees"]').attr('data-crf');
                    var reg_fees = (dataset.reg_fees ? dataset.reg_fees : (crf != 'undefined' && crf != '' ? crf : ''));
                    $('#editCourseCreationInstForm select[name="academic_year_id"]').val(dataset.academic_year_id ? dataset.academic_year_id : '');
                    $('#editCourseCreationInstForm input[name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                    $('#editCourseCreationInstForm input[name="end_date"]').val(dataset.end_date ? dataset.end_date : '');
                    $('#editCourseCreationInstForm input[name="total_teaching_week"]').val(dataset.total_teaching_week ? dataset.total_teaching_week : '');
                    $('#editCourseCreationInstForm input[name="fees"]').val(fees);
                    $('#editCourseCreationInstForm input[name="reg_fees"]').val(reg_fees);
                    

                    $('#editCourseCreationInstForm input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });


        $('#editCourseCreationInstForm').on('submit', function(e){
            e.preventDefault();
            const formDF = document.getElementById('editCourseCreationInstForm');

            $('#editCourseCreationInstForm').find('input').removeClass('border-danger')
            $('#editCourseCreationInstForm').find('.acc__input-error').html('')

            document.querySelector('#updateCCIN').setAttribute('disabled', 'disabled');
            document.querySelector('#updateCCIN svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formDF);

            axios({
                method: "post",
                url: route('course.creation.instance.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateCCIN').removeAttribute('disabled');
                document.querySelector('#updateCCIN svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    editCourseCreationInstModal.hide();
                    courseCreationINListTable.init();
                    
                    succModalCCIN.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Creation Instance Data Successfully Updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateCCIN').removeAttribute('disabled');
                document.querySelector('#updateCCIN svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editCourseCreationInstForm .${key}`).addClass('border-danger')
                            $(`#editCourseCreationInstForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });


        $('#addCourseCreationInstForm').on('submit', function(e){
            e.preventDefault();
            const formDF = document.getElementById('addCourseCreationInstForm');

            $('#addCourseCreationInstForm').find('input').removeClass('border-danger')
            $('#addCourseCreationInstForm').find('.acc__input-error').html('')

            document.querySelector('#saveCCIN').setAttribute('disabled', 'disabled');
            document.querySelector('#saveCCIN svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formDF);

            axios({
                method: "post",
                url: route('course.creation.instance.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveCCIN').removeAttribute('disabled');
                document.querySelector('#saveCCIN svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    addCourseCreationInstModal.hide();
                    courseCreationINListTable.init();
                    
                    succModalCCIN.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Creation Instance Data Successfully Inserted.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#saveCCIN').removeAttribute('disabled');
                document.querySelector('#saveCCIN svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addCourseCreationInstForm .${key}`).addClass('border-danger')
                            $(`#addCourseCreationInstForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });
    }
})();

(function () {

    var tomSelectArray = [];
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

    
    //var employment_status = new TomSelect('#employment_status', tomOptions);

    $('.lccTom').each(function(){
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
    })

    if($('#courseCreationInstTable').length > 0){
        $(".datepicker.itdp").each(function () {
            var maskOptions = {
                mask: '00-00-0000'
            };
            var mask = IMask(this, maskOptions);
        });
    
        $('#courseCreationInstTable').on('click', '.addInstanceTermBtn', function(){
            var $this = $(this);
            var dataID = $this.attr('data-id');
            var academicYearId = $this.attr('data-academic_year_id');
            $('#instancetermAddModal input[name="course_creation_instance_id"]').val(dataID);

            $.each(tomSelectArray,(i) =>{
                
                    tomSelectArray[i].setValue("")
                
            });
        });
    
        const instanceSuccModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const instancetermAddModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#instancetermAddModal"));
        const instancetermEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#instancetermEditModal"));
        const confModalITNS = tailwind.Modal.getOrCreateInstance(document.querySelector("#instancetermConfirmModal"));
        let confModalDelTitle = 'Are you sure?';
    
        const instancetermAddModalEl = document.getElementById('instancetermAddModal')
        instancetermAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#instancetermAddModal .acc__input-error').html('');
            $('#instancetermAddModal .modal-body input').val('');
            $('#instancetermAddModal .modal-body select').val('');
        });
        
        const instancetermEditModalEl = document.getElementById('instancetermEditModal')
        instancetermEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#instancetermEditModal .acc__input-error').html('');
            $('#instancetermEditModal .modal-body input').val('');
            $('#instancetermEditModal .modal-body select').val('');

            $('#instancetermEditModal input[name="id"]').val('0');
            $('#instancetermEditModal input[name="course_creation_instance_id"]').val('0');
        });
    
        const confModalITNSEl = document.getElementById('instancetermConfirmModal')
        confModalITNSEl.addEventListener('hidden.tw.modal', function(event){
            $('#instancetermConfirmModal .agreeWith').attr('data-id', '0');
            $('#instancetermConfirmModal .agreeWith').attr('data-action', 'none');
        });

        $('#instancetermAddModal select#term_declaration_id').on('change', function(e){
            
            let $editBtn = $(this);
            let editId = $editBtn.val();
            if(editId=="") {
                $('#instancetermAddModal .modal-body input').val('');
                $('#instancetermAddModal .modal-body select').val('');
            }else{
                axios({
                    method: "get",
                    url: route("term-declaration.edit", editId),
                    headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
                }).then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;

                        $('#instancetermAddModal input[name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                        $('#instancetermAddModal input[name="end_date"]').val(dataset.end_date ? dataset.end_date : '');
                        $('#instancetermAddModal input[name="total_teaching_weeks"]').val(dataset.total_teaching_weeks ? dataset.total_teaching_weeks : '');
                        $('#instancetermAddModal input[name="teaching_start_date"]').val(dataset.teaching_start_date ? dataset.teaching_start_date : '');
                        $('#instancetermAddModal input[name="teaching_end_date"]').val(dataset.teaching_end_date ? dataset.teaching_end_date : '');
                        $('#instancetermAddModal input[name="revision_start_date"]').val(dataset.revision_start_date ? dataset.revision_start_date : '');
                        $('#instancetermAddModal input[name="revision_end_date"]').val(dataset.revision_end_date ? dataset.revision_end_date : '');

                    }
                }).catch((error) => {
                    console.log(error);
                });
            }
        });
        $('#instancetermAddForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('instancetermAddForm');

            $('#instancetermAddForm').find('input').removeClass('border-danger')
            $('#instancetermAddForm').find('.acc__input-error').html('')

            document.querySelector('#saveInstanceTerm').setAttribute('disabled', 'disabled');
            document.querySelector('#saveInstanceTerm svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('instance.term.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveInstanceTerm').removeAttribute('disabled');
                document.querySelector('#saveInstanceTerm svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    instancetermAddModal.hide();
                    courseCreationINListTable.init();
                    
                    instanceSuccModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Instance Term Data Successfully Inserted.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#saveInstanceTerm').removeAttribute('disabled');
                document.querySelector('#saveInstanceTerm svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#instancetermAddForm .${key}`).addClass('border-danger')
                            $(`#instancetermAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#courseCreationInstTable").on("click", ".editTermBtn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");
            
            axios({
                method: "get",
                url: route("instance.term.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    console.log(tomSelectArray);
                    document.querySelector('select[name="term_declaration_id"]').tomselect.setValue(dataset.term_declaration_id)
                    //$('#instancetermEditModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    $.each(tomSelectArray,(i) =>{
                        if(tomSelectArray[i].inputId == "edit_term_declaration_id") {
                            tomSelectArray[i].setValue(dataset.term_declaration_id);
                        } else {
                            tomSelectArray[i].clear()
                        }
                    });
                    
                    $('#instancetermEditModal select[name="term_declaration_id"]').val(dataset.term_declaration_id ? dataset.term_declaration_id : '');
                    $('#instancetermEditModal select[name="session_term"]').val(dataset.session_term ? dataset.session_term : '');
                    $('#instancetermEditModal input[name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                    $('#instancetermEditModal input[name="end_date"]').val(dataset.end_date ? dataset.end_date : '');
                    $('#instancetermEditModal input[name="total_teaching_weeks"]').val(dataset.total_teaching_weeks ? dataset.total_teaching_weeks : '');
                    $('#instancetermEditModal input[name="teaching_start_date"]').val(dataset.teaching_start_date ? dataset.teaching_start_date : '');
                    $('#instancetermEditModal input[name="teaching_end_date"]').val(dataset.teaching_end_date ? dataset.teaching_end_date : '');
                    $('#instancetermEditModal input[name="revision_start_date"]').val(dataset.revision_start_date ? dataset.revision_start_date : '');
                    $('#instancetermEditModal input[name="revision_end_date"]').val(dataset.revision_end_date ? dataset.revision_end_date : '');

                    $('#instancetermEditModal input[name="course_creation_instance_id"]').val(dataset.course_creation_instance_id ? dataset.course_creation_instance_id : 0);
                    $('#instancetermEditModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#instancetermEditForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('instancetermEditForm');

            $('#instancetermEditForm').find('input').removeClass('border-danger')
            $('#instancetermEditForm').find('.acc__input-error').html('')

            document.querySelector('#updateInstanceTerm').setAttribute('disabled', 'disabled');
            document.querySelector('#updateInstanceTerm svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('instance.term.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateInstanceTerm').removeAttribute('disabled');
                document.querySelector('#updateInstanceTerm svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    instancetermEditModal.hide();
                    courseCreationINListTable.init();
                    
                    instanceSuccModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Instance Term Data successfully updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateInstanceTerm').removeAttribute('disabled');
                document.querySelector('#updateInstanceTerm svg').style.cssText = 'display: none;';
                if(error.response){
                    if(error.response.status == 422){
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#instancetermEditForm .${key}`).addClass('border-danger')
                            $(`#instancetermEditForm  .error-${key}`).html(val)
                        }
                    }else{
                        console.log('error');
                    }
                }
            });
        });


        // Delete Room
        $('#courseCreationInstTable').on('click', '.deleteTermBtn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModalITNS.show();
            document.getElementById('instancetermConfirmModal').addEventListener('shown.tw.modal', function(event){
                $('#instancetermConfirmModal .instancetermConfModTitle').html(confModalDelTitle);
                $('#instancetermConfirmModal .instancetermConfModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#instancetermConfirmModal .instancetermAgreeWith').attr('data-id', rowID);
                $('#instancetermConfirmModal .instancetermAgreeWith').attr('data-action', 'DELETE');
            });
        });

        /*$('#instancetermTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let coursecreationinstanceID = $statusBTN.attr('data-id');

            confModalITNS.show();
            document.getElementById('instancetermConfirmModal').addEventListener('shown.tw.modal', function(event){
                $('#instancetermConfirmModal .instancetermConfModTitle').html(confModalDelTitle);
                $('#instancetermConfirmModal .instancetermConfModDesc').html('Do you really want to restore these record?');
                $('#instancetermConfirmModal .instancetermAgreeWith').attr('data-id', coursecreationinstanceID);
                $('#instancetermConfirmModal .instancetermAgreeWith').attr('data-action', 'RESTORE');
            });
        });*/

        // Confirm Modal Action
        $('#instancetermConfirmModal .instancetermAgreeWith').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#instancetermConfirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('instance.term.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#instancetermConfirmModal button').removeAttr('disabled');
                        confModalITNS.hide();

                        instanceSuccModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Instance term data successfully deleted!');
                        });
                    }
                    courseCreationINListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('instance.term.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#instancetermConfirmModal button').removeAttr('disabled');
                        confModalITNS.hide();

                        instanceSuccModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Instance term data Successfully Restored!');
                        });
                    }
                    courseCreationINListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })
    }
})()