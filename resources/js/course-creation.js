import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var courseCreationListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-01").val() != "" ? $("#query-01").val() : "";
        let status = $("#status-01").val() != "" ? $("#status-01").val() : "";
        let course = $("#course-01").val() != "" ? $("#course-01").val() : "";
        let semester = $("#semester-01").val() != "" ? $("#semester-01").val() : "";

        let tableContent = new Tabulator("#courseCreationTableId", {
            ajaxURL: route("course.creation.list"),
            ajaxParams: { querystr: querystr, status: status, course: course, semester: semester},
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
                    width: "80",
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                },
                {
                    title: "Qualification",
                    field: "qualification",
                    headerHozAlign: "left",
                },
                {
                    title: "Semester",
                    field: "semester",
                    headerHozAlign: "left",
                },
                {
                    title: "Duration",
                    field: "duration",
                    headerHozAlign: "left",
                },
                {
                    title: "Unit Length",
                    field: "unit_length",
                    headerHozAlign: "left",
                },
                {
                    title: "Venue (s)",
                    field: "id",
                    headerSort: false,
                    hozAlign: "left",
                    headerHozAlign: "left",
                    width: "180",
                    formatter(cell, formatterParams) {                        
                        var thml = "";
                        $(cell.getData().venues).each(function(index, data) {
                        thml+=`<div class="whitespace-nowrap">${data.name}</div>
                                <div class="text-slate-500 text-xs whitespace-nowrap mb-1">${data.pivot.slc_code}</div>`;

                        });
                        return thml;
                    },
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
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns +='<a href="'+route('course.creation.show', cell.getData().id)+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editCourseCreationModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Course Creations",
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
    if($('#courseCreationTableId').length > 0){
        // Init Table
        courseCreationListTable.init();

        // Filter function
        function filterHTMLForm() {
            courseCreationListTable.init();
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
            $("#course-01").val("");
            $("#semester-01").val("");
            $("#status-01").val("1");
            filterHTMLForm();
        });


        const addCourseCreationModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCourseCreationModal"));
        const editCourseCreationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCourseCreationModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        const confirmModalVenue = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalVenue"));

        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescription = 'Do you really want to re-store these records? Click agree to continue.';

        const addCourseCreationModalEl = document.getElementById('addCourseCreationModal')
        addCourseCreationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addCourseCreationModal .acc__input-error').html('');
            $('#addCourseCreationModal input:not([type="checkbox"]').val('');
            $('#addCourseCreationModal select').val('');
            $('#addCourseCreationModal [name="has_evening_and_weekend"]').prop('checked', false);
            $('#addCourseCreationModal .hew_label').text('No');

            $('#addCourseCreationModal [name="is_workplacement"]').prop('checked', false);
            $('#addCourseCreationModal .iwkp_label').text('No');

            $('#addCourseCreationModal .requiredHoursWrap').fadeOut('fast', function(){
                $('[name="required_hours"]', this).val('');
            })
        });
        
        const editCourseCreationModalEl = document.getElementById('editCourseCreationModal')
        editCourseCreationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editCourseCreationModal .acc__input-error').html('');
            $('#editCourseCreationModal input:not([type="checkbox"]').val('');
            $('#editCourseCreationModal select').val('');
            $('#editCourseCreationModal input[name="id"]').val('0');
            $('#editCourseCreationModal [name="has_evening_and_weekend"]').prop('checked', false);
            $('#editCourseCreationModal .hew_label').text('No');
            
            $('#editCourseCreationModal [name="is_workplacement"]').prop('checked', false);
            $('#editCourseCreationModal .iwkp_label').text('No');

            $('#editCourseCreationModal .requiredHoursWrap').fadeOut('fast', function(){
                $('[name="required_hours"]', this).val('');
            })
        });


        $('#courseCreationTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        $('#courseCreationTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record?');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
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
                    url: route('course.creation.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Course creation data successfully deleted.');
                        });
                    }
                    courseCreationListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('course.creation.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Course Creation Data Successfully Restored!');
                        });
                    }
                    courseCreationListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#is_workplacement').on('change', function(){
            if($(this).prop('checked')){
                $(this).siblings('label.iwkp_label').text('Yes');
                $('#addCourseCreationForm .requiredHoursWrap').fadeIn('fast', function(){
                    $('[name="required_hours"]', this).val('');
                })
            }else{
                $(this).siblings('label.iwkp_label').text('No');
                $('#addCourseCreationForm .requiredHoursWrap').fadeOut('fast', function(){
                    $('[name="required_hours"]', this).val('');
                })
            }
        });

        $('#has_evening_and_weekend').on('change', function(){
            if($(this).prop('checked')){
                $(this).siblings('label.hew_label').text('Yes');
            }else{
                $(this).siblings('label.hew_label').text('No');
            }
        });

        $('#edit_is_workplacement').on('change', function(){
            if($(this).prop('checked')){
                $(this).siblings('label.iwkp_label').text('Yes');
                $('#editCourseCreationForm .requiredHoursWrap').fadeIn('fast', function(){
                    $('[name="required_hours"]', this).val('');
                })
            }else{
                $(this).siblings('label.iwkp_label').text('No');
                $('#editCourseCreationForm .requiredHoursWrap').fadeOut('fast', function(){
                    $('[name="required_hours"]', this).val('');
                })
            }
        });

        $('#edit_has_evening_and_weekend').on('change', function(){
            if($(this).prop('checked')){
                $(this).siblings('label.hew_label').text('Yes');
            }else{
                $(this).siblings('label.hew_label').text('No');
            }
        });

        $("#courseCreationTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("course.creation.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editCourseCreationModal input[name="duration"]').val(dataset.duration ? dataset.duration : '');
                    $('#editCourseCreationModal select[name="semester_id"]').val(dataset.semester_id ? dataset.semester_id : '');
                    $('#editCourseCreationModal select[name="course_creation_qualification_id"]').val(dataset.course_creation_qualification_id ? dataset.course_creation_qualification_id : '');
                    $('#editCourseCreationModal select[name="course_id"]').val(dataset.course_id ? dataset.course_id : '');
                    $('#editCourseCreationModal select[name="unit_length"]').val(dataset.unit_length ? dataset.unit_length : '');
                    $('#editCourseCreationModal input[name="slc_code"]').val(dataset.slc_code ? dataset.slc_code : '');

                    $('#editCourseCreationModal select[name="venue_id"]').val(dataset.venue_id ? dataset.venue_id : '');
                    $('#editCourseCreationModal input[name="fees"]').val(dataset.fees ? dataset.fees : '');
                    $('#editCourseCreationModal input[name="reg_fees"]').val(dataset.reg_fees ? dataset.reg_fees : '');
                    
                    if(dataset.has_evening_and_weekend == 1){
                        $('#editCourseCreationModal input[name="has_evening_and_weekend"]').prop('checked', true);
                        $('#editCourseCreationModal .hew_label').text('Yes');
                    }else{
                        $('#editCourseCreationModal input[name="has_evening_and_weekend"]').prop('checked', false);
                        $('#editCourseCreationModal .hew_label').text('No');
                    }
                    if(dataset.is_workplacement == 1){
                        $('#editCourseCreationModal input[name="is_workplacement"]').prop('checked', true);
                        $('#editCourseCreationModal .iwkp_label').text('Yes');
                        $('#editCourseCreationForm .requiredHoursWrap').fadeIn('fast', function(){
                            $('[name="required_hours"]', this).val(dataset.required_hours ? dataset.required_hours : '');
                        })
                    }else{
                        $('#editCourseCreationModal input[name="is_workplacement"]').prop('checked', false);
                        $('#editCourseCreationModal .iwkp_label').text('No');
                        $('#editCourseCreationForm .requiredHoursWrap').fadeOut('fast', function(){
                            $('[name="required_hours"]', this).val('');
                        })
                    }

                    $('#editCourseCreationModal input[name="id"]').val(editId);
                    let venues = response.data.venues;
                    $('table#edit-newvenue').html('');
                    $('table#edit-newvenue').html('<tr></tr>');
                    venues.forEach((e, i) => {
                    if(e.pivot.deleted_at==null) {
                    let html='<tr id="'+e.pivot.id+'"><td class="col-span-5">\
                                <label for="venue_id'+e.pivot.id+'" class="form-label">Venue</label>\
                                <select id="venue_id'+e.pivot.id+'" name="venue_id[]" class="form-control w-full">\
                                    <option selected value="'+e.id+'">'+e.name+'</option>\
                                </select>\
                            </td>\
                            <td class="col-span-5">\
                                <label for="slc_code'+e.pivot.id+'" class="form-label">SLC Code</label>\
                                <input id="slc_code'+e.pivot.id+'" type="text" name="slc_code[]" value="'+e.pivot.slc_code+'" class="form-control w-full">\
                            </td>\
                            <td class="col-span-2">\
                                <button id="delete-'+e.pivot.id+'" type="button" data-id="'+e.pivot.id+'"  class="btnDelete btn btn-danger text-white btn-rounded ml-1 p-0 w-8 h-8 mt-7"><i data-lucide="Trash2" class="w-4 h-4"></i></button>\
                            </td></tr>';
                            $('table#edit-newvenue tr:last').after(html); 
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                        }
                    });
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#editCourseCreationForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('editCourseCreationForm');
        
            document.querySelector('#updateCourseCreation').setAttribute('disabled', 'disabled');
            document.querySelector("#updateCourseCreation svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('course.creation.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateCourseCreation').removeAttribute('disabled');
                document.querySelector("#updateCourseCreation svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    editCourseCreationModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Course creation data successfully updated.');
                    });                
                        
                }
                courseCreationListTable.init();
            }).catch(error => {
                document.querySelector('#updateCourseCreation').removeAttribute('disabled');
                document.querySelector("#updateCourseCreation svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editCourseCreationForm .${key}`).addClass('border-danger')
                            $(`#editCourseCreationForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#addCourseCreationForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addCourseCreationForm');
        
            document.querySelector('#saveCourseCreation').setAttribute('disabled', 'disabled');
            document.querySelector("#saveCourseCreation svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('course.creation.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveCourseCreation').removeAttribute('disabled');
                document.querySelector("#saveCourseCreation svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addCourseCreationModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Course creation data successfully inserted.');
                    });                
                        
                }
                courseCreationListTable.init();
            }).catch(error => {
                document.querySelector('#saveCourseCreation').removeAttribute('disabled');
                document.querySelector("#saveCourseCreation svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addCourseCreationForm .${key}`).addClass('border-danger')
                            $(`#addCourseCreationForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("table#add-newvenue").on('click','.btnDelete',function(){
            let tthis = $(this);
                tthis.closest('tr').remove();
            
            
        });
        $('.venueAdd').on('click', function(e){
            e.preventDefault();
            document.querySelector('.venueAdd').setAttribute('disabled', 'disabled');
            document.querySelector(".venueAdd svg.load-icon").style.cssText ="display: inline-block;";
            axios({
                method: "get",
                url: route("venues.all"),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                document.querySelector('.venueAdd').removeAttribute('disabled');
                document.querySelector(".venueAdd svg.load-icon").style.cssText ="display:none;";
            
                if (response.status == 200) {

                    let dataset = response.data
                    let randomId = Math.floor(Math.random() * 101);
                    let html='<tr><td class="col-span-5">\
                                <label for="venue_id'+randomId+'" class="form-label">Venue</label>\
                                <select id="venue_id'+randomId+'" name="venue_id[]" class="form-control w-full">\
                                    <option value="">Please Select</option>'
                                    dataset.forEach((e, i) => {
                                        html+='<option value="'+e.id+'">'+e.name+'</option>'
                                    });
                    
                                 html+='</select>\
                            </td>\
                            <td class="col-span-5">\
                                <label for="slc_code'+randomId+'" class="form-label">SLC Code</label>\
                                <input id="slc_code'+randomId+'" type="text" name="slc_code[]" class="form-control w-full">\
                            </td>\
                            <td class="col-span-2">\
                                <button type="button" data-id="0"  class="btnDelete btn btn-danger text-white btn-rounded ml-1 p-0 w-8 h-8 mt-7"><i data-lucide="Trash2" class="w-4 h-4"></i></button>\
                            </td></tr>';
                            $('table#add-newvenue tr:last').after(html); 
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                            

                }
            })
            .catch((error) => {
                console.log(error);
            });
        })

        $("table#edit-newvenue").on('click','.btnDelete',function(){
            let tthis = $(this);
            let getId = tthis.data('id');
            if(getId==0) {
                tthis.closest('tr').remove();
            } else {
                confirmModalVenue.show();
                document.getElementById('confirmModalVenue').addEventListener('shown.tw.modal', function(event){
                    $('#confirmModalVenue .confModTitle').html(confModalDelTitle);
                    $('#confirmModalVenue .confModDesc').html('Do you really want to delete these venue? If yes, the please click on agree btn.');
                    $('#confirmModalVenue .agreeWithVenue').attr('data-id', getId);
                    $('#confirmModalVenue .agreeWithVenue').attr('data-action', 'DELETE');
                });
            }
            
         });
                 // Confirm Modal Action
        $('#confirmModalVenue .agreeWithVenue').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');
            let btnId="#delete-"+recordID;
                
            $('#confirmModalVenue button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('course.creation.venue.destroy', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalVenue button').removeAttr('disabled');
                        confirmModalVenue.hide();
                        $(btnId).closest('tr').remove();
                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Venue successfully deleted.');
                        });
                    }
                    courseCreationListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('course.creation.venue.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Course Creation Data Successfully Restored!');
                        });
                    }
                    courseCreationListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('.venueAddForEdit').on('click', function(e){
            e.preventDefault();
            document.querySelector('.venueAddForEdit').setAttribute('disabled', 'disabled');
            document.querySelector(".venueAddForEdit svg.load-icon").style.cssText ="display: inline-block;";
            axios({
                method: "get",
                url: route("venues.all"),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                document.querySelector('.venueAddForEdit').removeAttribute('disabled');
                document.querySelector(".venueAddForEdit svg.load-icon").style.cssText ="display:none;";
            
                if (response.status == 200) {

                    let dataset = response.data
                    let randomId = Math.floor(Math.random() * 101);
                    let html='<tr id="'+randomId+'"><td class="col-span-5">\
                                <label for="venue_id'+randomId+'" class="form-label">Venue</label>\
                                <select id="venue_id'+randomId+'" name="venue_id[]" class="form-control w-full">\
                                    <option value="">Please Select</option>'
                                    dataset.forEach((e, i) => {
                                        html+='<option value="'+e.id+'">'+e.name+'</option>'
                                    });
                    
                                 html+='</select>\
                            </td>\
                            <td class="col-span-5">\
                                <label for="slc_code'+randomId+'" class="form-label">SLC Code</label>\
                                <input id="slc_code'+randomId+'" type="text" name="slc_code[]" class="form-control w-full">\
                            </td>\
                            <td class="col-span-2">\
                                <button type="button" data-id="0"  class="btnDelete btn btn-danger text-white btn-rounded ml-1 p-0 w-8 h-8 mt-7"><i data-lucide="Trash2" class="w-4 h-4"></i></button>\
                            </td></tr>';
                            $('table#edit-newvenue tr:last').after(html); 
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                            

                }
            })
            .catch((error) => {
                console.log(error);
            });
        })
    }
})()