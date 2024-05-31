import Dropzone from "dropzone";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");

var employmentHistoryTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#employmentHistoryTable").attr('data-applicant') != "" ? $("#employmentHistoryTable").attr('data-applicant') : "0";
        let querystr = $("#query-EH").val() != "" ? $("#query-EH").val() : "";
        let status = $("#status-EH").val() != "" ? $("#status-EH").val() : "";

        let tableContent = new Tabulator("#employmentHistoryTable", {
            ajaxURL: route("employment.list"),
            ajaxParams: { applicantId: applicantId, querystr: querystr, status: status},
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
                    title: "Organization",
                    field: "company_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "company_phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Position",
                    field: "position",
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
                    title: "Address",
                    field: "address",
                    headerHozAlign: "left",
                    width: "180",
                    formatter(cell, formatterParams) {   
                        return '<div class="whitespace-nowrap">'+cell.getData().address+'</div>';
                    }
                },
                {
                    title: "Contact Person",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Position",
                    field: "contact_position",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "contact_phone",
                    headerHozAlign: "left",
                },

            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
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
        $("#tabulator-export-csv-EH").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EH").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EH").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Employment History Details",
            });
        });

        $("#tabulator-export-html-EH").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EH").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

//if(document.getElementById('educationQualTable').length > 0){
var educationQualTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#educationQualTable").attr('data-applicant') != "" ? $("#educationQualTable").attr('data-applicant') : "0";
        let querystr = $("#query-EQ").val() != "" ? $("#query-EQ").val() : "";
        let status = $("#status-EQ").val() != "" ? $("#status-EQ").val() : "";

        let tableContent = new Tabulator("#educationQualTable", {
            ajaxURL: route("qualification.list"),
            ajaxParams: { applicantId: applicantId, querystr: querystr, status: status},
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
                    width: "110",
                },
                {
                    title: "Awarding Body",
                    field: "awarding_body",
                    headerHozAlign: "left",
                },
                {
                    title: "Highest Academic Qualification",
                    field: "highest_academic",
                    headerHozAlign: "left",
                },
                {
                    title: "Subjects",
                    field: "subjects",
                    headerHozAlign: "left",
                },
                {
                    title: "Result",
                    field: "result",
                    headerHozAlign: "left",
                },
                {
                    title: "Award Date",
                    field: "degree_award_date",
                    headerHozAlign: "left",
                },
 
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
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
        $("#tabulator-export-csv-EQ").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EQ").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EQ").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Education Qualification Details",
            });
        });

        $("#tabulator-export-html-EQ").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EQ").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();
//}

const editModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

$(document).on("click", ".interview-result", function (e) { 
        e.preventDefault();
        //interviewId = $(this).attr("data-id");
        document.getElementById('id').value = $(this).attr("data-id");
    
});
$(document).on("click", ".interview-taskend", function (e) { 
            
            e.preventDefault();

            const theId = $(this).attr("data-id");
            console.log(theId);
            axios({
                method: "post",
                url: route('applicant.interview.task.update'),
                data: {
                  id: theId
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {

                if (response.status == 200) {
                    editModal.hide();
                    succModal.show();
                    
                    let status = response.data.status;
                    document.getElementById("ProgressStatus").innerHTML = status;
                    
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(response.data.msg);
                            $("#successModal .successModalDesc").html('success');
                        });    
                        
                        $("#magic-button1").addClass('hidden');
                        $("#magic-button2").addClass('hidden');
                        $("#magic-button3").addClass('hidden');
                }
                
                //interviewListTable.init();

            }).catch(error => {
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

});

$(document).on("click", ".interview-start", function (e) { 
            
    e.preventDefault();

    const theId = $(this).attr("data-id");
    console.log(theId);
    axios({
        method: "post",
        url: route('applicant.interview.start'),
        data: {
          id: theId
        },
        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {

        if (response.status == 200) {
            
            let startTime = response.data.data.start;
            let ProgressStatus = response.data.data.status;
            document.getElementById("progressStart").innerHTML = startTime;
            document.getElementById("ProgressStatus").innerHTML = ProgressStatus;
            document.querySelector(".interview-start").setAttribute('disabled', 'disabled');
            editModal.hide();
            succModal.show();
            document.getElementById("successModal")
                .addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html(response.data.msg);
                    $("#successModal .successModalDesc").html('success');
                });   
                
            
        }

        //interviewListTable.init();

    }).catch(error => {
        
        if (error.response) {
            if (error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#${key}`).addClass('border-danger')
                    $(`#error-${key}`).html(val)
                }
            } else {
                console.log('error');
            }
        }
    });

});


$(document).on("click", ".interview-end", function (e) { 
            
    e.preventDefault();

    const theId = $(this).attr("data-id");
    
    axios({
        method: "post",
        url: route('applicant.interview.end'),
        data: {
          id: theId
        },
        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {

        if (response.status == 200) {
            editModal.hide();
            succModal.show();
            
            let endTime = response.data.data.end;
            document.getElementById("progressEnd").innerHTML = endTime;

            document.getElementById("successModal")
                .addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html(response.data.msg);
                    $("#successModal .successModalDesc").html('success');
                });    

                $("#magic-button1").addClass('hidden');
                $("#magic-button2").removeClass('hidden');
        }

        //interviewListTable.init();

    }).catch(error => {
        
        if (error.response) {
            if (error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#${key}`).addClass('border-danger')
                    $(`#error-${key}`).html(val)
                }
            } else {
                console.log('error');
            }
        }
    });

});


(function () {
    if($('#educationQualTable').length > 0){
        if($('#educationQualTable').hasClass('activeTable')){
            educationQualTable.init();
        }
        // Filter function
        function filterHTMLFormEQ() {
            educationQualTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-EQ").on("click", function (event) {
            filterHTMLFormEQ();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EQ").on("click", function (event) {
            $("#query-EQ").val("");
            $("#status-EQ").val("1");
            filterHTMLFormEQ();
        });



    }

    if($('#employmentHistoryTable').length > 0){
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
        
        if($('#employmentHistoryTable').hasClass('activeTable')){
            employmentHistoryTable.init();
        }

        // Filter function
        function filterHTMLFormEH() {
            employmentHistoryTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-EH").on("click", function (event) {
            filterHTMLFormEH();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EH").on("click", function (event) {
            $("#query-EH").val("");
            $("#status-EH").val("1");
            filterHTMLFormEH();
        });

        new TomSelect('#employment_status', tomOptions);



    }
    
    // To get value of interview result field
    var interview_result = document.getElementById('interview_result');
    var resultValue = document.getElementById('resultValue');

    var updateInterviewResult = function () {
        resultValue.value = interview_result.value;
    }

    if (interview_result.addEventListener) {
        interview_result.addEventListener('change', function () {
            updateInterviewResult();
        });
    }

    $('#errorModal .errorCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            errorModal.hide();
            window.location.reload();
        }else{
            errorModal.hide();
        }
    });

    // Start Dropzone
    if($("#editForm").length > 0){
        let dzError = false;
        let errorResponse = {};
        Dropzone.autoDiscover = false;
        Dropzone.options.editForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 20,
            parallelUploads: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
            addRemoveLinks: true,
            thumbnailWidth: 100,
            thumbnailHeight: 100,
        };
        
        let options = {
            accept: (file, done) => {
                console.log("Uploaded");             
                done();
            },
        };

        var drzn1 = new Dropzone('#editForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#editForm .modal-content .uploadError').remove();
            $('#editForm .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 1 file at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#editForm .modal-content .uploadError').remove();
            }, 4000)
        });

        drzn1.on("error", function(file, response){
            dzError = true;
            errorResponse = response
        });

        drzn1.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on('queuecomplete', function(){
            $('#update').removeAttr('disabled');
            document.querySelector("#update svg").style.cssText ="display: none;";

            editModal.hide();
            if(!dzError){
                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Success!" );
                    $("#successModal .successModalDesc").html('Successfully uploaded.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    succModal.hide();
                    window.location.reload();
                }, 1500);
            }else{
                console.log(errorResponse);
                errorModal.show();
                document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                    $("#errorModal .errorModalTitle").html("Error!" );
                    $("#errorModal .errorModalDesc").html(errorResponse.message);
                    $("#errorModal .errorCloser").attr('data-action', 'DISMISS');
                });
                // setTimeout(function(){
                //     errorModal.hide();
                //     window.location.reload();
                // }, 5000);
                drzn1.removeAllFiles(true);
            }
        })

        $('#update').on('click', function(e) {
            e.preventDefault();
            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector("#update svg").style.cssText ="display: inline-block;";
            let dataDropZones = drzn1.getAcceptedFiles()
            
            
            if($('#editModal [name="resultValue"]').val() !=""){
                var result = $('#editModal [name="resultValue"]').val();
                $('#editModal input[name="resultValue"]').val(result)
                if(dataDropZones.length>0) {
                    drzn1.processQueue();
                }else {
                    $('#editModal .modal-content .modal-body').prepend('<div id="dropZoneError" class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i>Please add a file before submit.</div>');
                    
                }
                
                $("#magic-button2").addClass('hidden');
                $("#magic-button3").removeClass('hidden');
            }else{
                document.querySelector('#update').removeAttribute('disabled');
                document.querySelector("#update svg").style.cssText ="display: none;";
                $('#editModal .modal-content .uploadError').remove();
                $('#editModal .modal-content .modal-body').prepend('<div id="resultTypeDropZoneError" class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i>Please select result type.</div>');
                
                if(dataDropZones.length<=0) {
                    $('#editModal .modal-content .modal-body').prepend('<div id="dropZoneError" class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i>Please add a file before submit.</div>');
                    
                }
                
                setTimeout(function(){
                    $('#editModal .modal-content .uploadError').remove();
                }, 5000)
            }

            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
            
        });

        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){

            let id =$('#confirmModal .agreeWith').attr('data-id');
            let actionDelete = $('#confirmModal .agreeWith').attr('data-action');
            console.log(actionDelete)
            console.log(id)
        });
        // Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function() {
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('applicant.interview.file.remove', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Uploaded interview file successfully deleted!');
                        });
                    }
                    document.getElementById('fileLoadedView').innerHTML='<i data-lucide="slash" class="w-5 h-5"></i>';
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }).catch(error =>{
                    console.log(error)
                });
            } 
        })
    }
    // End Dropzone

})()