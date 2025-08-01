import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Dropzone from "dropzone";

("use strict");
var studentVisitsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator

        let query_search = $("#query_search").val() != "" ? $("#query_search").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let studentId = $("#studentVisitsListTable").data("student");
        let tableContent = new Tabulator("#studentVisitsListTable", {
            ajaxURL: route("student.visits.list"),
            ajaxParams: { query_search: query_search, status: status, student_id: studentId },
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
                    title: "sl",
                    field: "sl",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: '80'
                },
                {
                    title: "Visit Type",
                    field: "visit_type",
                    headerHozAlign: "left",
                    width: '140',
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().visit_type+'</div>';
                    }
                },
                {
                    title: "Date",
                    field: "visit_date",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: '100'
                },
                {
                    title: "Attendance Deleted By",
                    field: "attendance_deleted_by",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: '250'
                },
                {
                    title: "Visit Duration",
                    field: "visit_duration",
                    headerHozAlign: "left",
                    width: '115',
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().visit_duration+'</div>';
                    }
                },
                
                {
                    title: "Module",
                    field: "module_name",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().module_name+'</div>';
                    }
                },
                {
                    title: "Notes",
                    field: "visit_notes",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().visit_notes+'</div>';
                    }
                },
                {
                    title: "Created By",
                    field: "created_by",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: '155',
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().created_by+'</div>';
                    }
                },
                {
                    title: "Last modified By",
                    field: "updated_by",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: '155',
                    formatter(cell, formatterParams){
                        return '<div class="font-medium">'+cell.getData().updated_by+'</div>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "120",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            
                            btns  = '<button data-id="' +cell.getData().id +'" type="button" class="show_btn btn-rounded btn btn-twitter text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                            if (cell.getData().edit_permission) {
                                btns += '<button data-id="' +cell.getData().id +'" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            }
                            if (cell.getData().delete_permission) {
                                btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                            }
                        }  else {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
                        return btns;
                    },
                }
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
            rowFormatter:function(row){
                var data = row.getData();
                
                /*if(data.active == 1){
                    //row.getElement().style.backgroundColor = "#d977061a";
                    row.getElement().style.backgroundColor = "#FFFFFF";
                }else if(data.active == 2){
                    row.getElement().style.backgroundColor = "#0d6efd33";
                }else if(data.active == 3){
                    row.getElement().style.backgroundColor = "#0d948833";
                }else{
                    row.getElement().style.backgroundColor = "#b91c1c33";
                }*/
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
                sheetName: "Title Details",
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
    let tomOptionsGlobal = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        maxOptions: null,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    
    const visitTypeId = new TomSelect('#visitTypeId', tomOptionsGlobal);
    const visitDurationId = new TomSelect('#visitDurationId', tomOptionsGlobal);
    const visitTypeIdEdit = new TomSelect('#visitTypeIdEdit', tomOptionsGlobal);
    const visitDurationIdEdit = new TomSelect('#visitDurationIdEdit', tomOptionsGlobal);
    const terms = new TomSelect('#terms', tomOptionsGlobal);
    const modules = new TomSelect('#modules', tomOptionsGlobal);
    const termsEdit = new TomSelect('#termsEdit', tomOptionsGlobal);
    const modulesEdit = new TomSelect('#modulesEdit', tomOptionsGlobal);
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const addVisitModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addVisitModal"));
    const editVisitModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editVisitModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
    const showVisitModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#showVisitModal"));
    
    visitTypeId.on('change', function() {
        if (visitTypeId.getValue() === 'academic') {
            $('.visit-student-info').removeClass('hidden');
        } else {
            $('.visit-student-info').addClass('hidden');
        }
    });

    visitTypeIdEdit.on('change', function() {
        if (visitTypeIdEdit.getValue() === 'academic') {
            $('.visit-student-info-edit').removeClass('hidden');
        } else {
            $('.visit-student-info-edit').addClass('hidden');
        }
    });


    terms.on('change', function() {
        modules.clearOptions();
        modules.setValue(""); // Reset selection

        const termId = terms.getValue();
        const studentId = $('#studentVisitsListTable').data('student');


            document.querySelector("#modulesContainer .loading-icon").classList.remove("hidden");
            modules.disable();
        if (termId) {
            axios({
                method: "get",
                url: route('student.visits.modules', [termId, studentId]),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                // Ensure response.data is an array of {value, text}
                document.querySelector("#modulesContainer .loading-icon").classList.add("hidden");
                modules.enable();
                if (response.status === 200) {
                    //modules.addOptions(response.data);

                    for (const [index, data] of Object.entries(response.data)) {
                        modules.addOption({value: data.value, text: data.text});
                    }
                } else {
                    //modules.addOptions([]);
                }
            }).catch(error => {
                console.error("Error fetching modules:", error);
                //modules.addOptions([]);
            });
        }
    });

    termsEdit.on('change', function() {

        modulesEdit.clearOptions();
        modulesEdit.setValue(""); // Reset selection

        const termId = termsEdit.getValue();
        const studentId = $('#studentVisitsListTable').data('student');


            document.querySelector("#modulesContainerEdit .loading-icon").classList.remove("hidden");
            modulesEdit.disable();
        if (termId) {
            axios({
                method: "get",
                url: route('student.visits.modules', [termId, studentId]),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                // Ensure response.data is an array of {value, text}
                document.querySelector("#modulesContainerEdit .loading-icon").classList.add("hidden");
                modulesEdit.enable();
                if (response.status === 200) {

                    for (const [index, data] of Object.entries(response.data)) {
                        modulesEdit.addOption({value: data.value, text: data.text});
                    }

                    // Set the value here, after options are loaded
                    if(window.EditModulevalue) {
                        modulesEdit.setValue(window.EditModulevalue);
                    }
                }
            }).catch(error => {
                console.error("Error fetching modules:", error);
            });
        }
    });

    // After initializing TomSelect for terms
    if (terms.options && Object.keys(terms.options).length > 0) {
        // Get the first option's value
        const optionKeys = Object.keys(terms.options).map(Number);
        // Find the highest value
        const highestValue = Math.max(...optionKeys);
        if (highestValue) {
            terms.setValue(String(highestValue)); // Set the highest value
            //terms.trigger('change');    // Trigger the change event
        }
    }

    const editVisitModalState = document.getElementById('editVisitModal');
    editVisitModalState.addEventListener('hide.tw.modal', function(event) {
        // Reset the form
        const form = document.getElementById('editVisiForm');
        if (form) form.reset();

        // Optionally clear custom errors and TomSelects
        $('#editVisitModal .acc__input-error').html('');
        $('#editVisitModal input[name="id"]').val('0');
        $('#editVisitModal select').val('');
        $('#editVisitModal textarea').val('');
        $('#editVisitModal input[type="text"]').val('');
        $('#editVisitModal input[type="checkbox"]').prop('checked', false);

        // If using TomSelect, reset their values too
        if (visitTypeIdEdit) {
            visitTypeIdEdit.clear(); 
            visitTypeIdEdit.setValue(""); // Set to "Please Select"
        }
        if (visitDurationIdEdit) {
            visitDurationIdEdit.clear();
            visitDurationIdEdit.setValue(""); // Set to "Please Select"
        }
    });
    
    /* START List Table INIT */
    studentVisitsListTable.init();

    // Filter function
    function filterHTMLForm() {
        studentVisitsListTable.init();
    }

    // On click go button
    $("#tabulator-html-filter-go").on("click", function (event) {
        filterHTMLForm();
    });

    // On reset filter form
    $("#tabulator-html-filter-reset").on("click", function (event) {
        $("#query_search").val("");
        filterHTMLForm();
    });

    $('#addVisitForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addVisitForm');

            document.querySelector('#saveVisit').setAttribute('disabled', 'disabled');
            document.querySelector("#saveVisit svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('student.visits.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveVisit').removeAttribute('disabled');
                document.querySelector("#saveVisit svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addVisitModal.hide();

                    succModal.show();
                    studentVisitsListTable.init();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                
            }).catch(error => {
                studentVisitsListTable.init();
                document.querySelector('#saveVisit').removeAttribute('disabled');
                document.querySelector("#saveVisit svg").style.cssText = "display: none;";
                $('#addVisitForm [class^="error-"]').html('');
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addVisitForm .${key}`).addClass('border-danger');
                            $(`#addVisitForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });

    $('#studentVisitsListTable').on('click','.edit_btn',function(){
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        axios({
            method: "get",
            url: route("student.visits.edit", recordId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;

                $('#editVisitModal textarea[name="visit_notes"]').val(dataset.visit_notes ? dataset.visit_notes : '');
                if (visitTypeIdEdit) {
                    visitTypeIdEdit.setValue(dataset.visit_type ? dataset.visit_type : "");
                }
                if (visitDurationIdEdit) {
                    visitDurationIdEdit.setValue(dataset.visit_duration ? dataset.visit_duration : "");
                }
                $('#editVisitModal input[name="visit_date"]').val(dataset.visit_date ? dataset.visit_date : '');
                
                $('#editVisitModal input[name="id"]').val(recordId);

                if (dataset.visit_type === 'academic') {
                    $('.visit-student-info-edit').removeClass('hidden');

                    const termEditId = dataset.term_declaration_id ? dataset.term_declaration_id : "";
                    if(termEditId) {
                        termsEdit.setValue(termEditId);
                        
                        window.EditModulevalue = dataset.plan_id ? dataset.plan_id : "";
                        //modulesEdit.setValue(EditModulevalue);
                    }
                } else {
                    $('.visit-student-info-edit').addClass('hidden');
                }
                editVisitModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });
    
    $('#studentVisitsListTable').on('click','.show_btn',function(){
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        axios({
            method: "get",
            url: route("student.visits.show", recordId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                for(const [key, value] of Object.entries(dataset)) {
                    if (value !== null && value !== undefined) {
                        $('#showVisitModal #' + toCamelCase(key) + 'Show').html(value);
                    }
                }
            }
            showVisitModal.show();
        }).catch((error) => {
            console.log(error);
        });
    });
    function toCamelCase(str) {
        return str.replace(/_([a-z])/g, (g) => g[1].toUpperCase());
    }

    $('#editVisitForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editVisitForm');
        let editId = $('#editVisitForm input[name="id"]').val();

        document.querySelector('#updateVisit').setAttribute('disabled', 'disabled');
        document.querySelector("#updateVisit svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.visits.update', editId),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateVisit').removeAttribute('disabled');
            document.querySelector("#updateVisit svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                // Update the table with the new data
                studentVisitsListTable.init();
                editVisitModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Visit updated.');
                });

                setTimeout(function(){
                    succModal.hide();
                }, 2000);
            }
            studentVisitsListTable.init();
        }).catch(error => {
            document.querySelector('#updateVisit').removeAttribute('disabled');
            document.querySelector("#updateVisit svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editVisitForm .${key}`).addClass('border-danger')
                        $(`#editVisitForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#studentVisitsListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? Click on agree btn to continue.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    // Restore Course
    $('#studentVisitsListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let dataID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree btn to continue.');
            $('#confirmModal .agreeWith').attr('data-id', dataID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('student.visits.destroy', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Item successfully deleted!');
                    });
                }
                studentVisitsListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('student.visits.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Item successfully restored!');
                    });
                }
                studentVisitsListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    

})();