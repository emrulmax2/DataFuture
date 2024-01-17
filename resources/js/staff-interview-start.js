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

(function () {
    let tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    
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


    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
    
    $('#interviewStartFromProfile').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('interviewStartFromProfile');
        let $applicant_id = $('#interviewStartFromProfile input[name="applicant_id"]').val();
    
        document.querySelector('#startInterviewSession').setAttribute('disabled', 'disabled');
        document.querySelector("#startInterviewSession svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('applicant.interview.start'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#startInterviewSession').removeAttribute('disabled');
            document.querySelector("#startInterviewSession svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Interview Started!");
                    $("#successModal .successModalDesc").html('Interview started at '+response.data.data.start);
                });                
                
                setTimeout(function(){
                    succModal.hide();
                }, 1400);
                let Data = response.data.data.ref;

                location.href=Data; 
            }
        }).catch(error => {
            document.querySelector('#startInterviewSession').removeAttribute('disabled');
            document.querySelector("#startInterviewSession svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#interviewStartFromProfile .${key}`).addClass('border-danger')
                        $(`#interviewStartFromProfile  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });


})()