import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var diversityListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let startdate = $("#startdate-DR").val() != "" ? $("#startdate-DR").val() : "";
        let enddate = $("#enddate-DR").val() != "" ? $("#enddate-DR").val() : "";
        let worktype = $("#employee_work_type_id-diversity").val() != "" ? $("#employee_work_type_id-diversity").val() : "";
        let department = $("#department_id-diversity").val() != "" ? $("#department_id-diversity").val() : "";
        let ethnicity = $("#ethnicity-DR").val() != "" ? $("#ethnicity-DR").val() : "";
        let nationality = $("#nationality-DR").val() != "" ? $("#nationality-DR").val() : "";
        let gender = $("#gender-DR").val() != "" ? $("#gender-DR").val() : "";
        let status = $("#status_id-DR").val() != "" ? $("#status_id-DR").val() : "";

        let tableContent = new Tabulator("#diversityListTable", {
            ajaxURL: route("hr.portal.reports.diversityreport.list"),
            ajaxParams: { ethnicity:ethnicity, nationality:nationality, startdate:startdate, worktype:worktype, department:department, gender:gender, enddate:enddate, status:status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printCopyStyle: true,
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
                    title: "Works Number",
                    field: "works_no",
                    headerHozAlign: "left",
                },
                {
                    title: "Gender",
                    field: "gender",
                    headerHozAlign: "left",
                },
                {
                    title: "Ethnicity",
                    field: "ethnicity",
                    headerHozAlign: "left",
                },
                {
                    title: "Nationality",
                    field: "nationality",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                }
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

        $("#tabulator-export-xlsx-DR").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "Diversity_Information.xlsx", {
                sheetName: "Diversity Information",
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
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove this " + values.length + " item?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    if($('#diversityListTable').length > 0){
        
        var worktypeDR = new TomSelect('#employee_work_type_id-diversity', tomOptions);
        var departmentDR = new TomSelect('#department_id-diversity', tomOptions);
        var ethnicityDR = new TomSelect('#ethnicity-DR', tomOptions);
        var nationalityDR = new TomSelect('#nationality-DR', tomOptions);
        var genderDR = new TomSelect('#gender-DR', tomOptions);

        // Init Table
        diversityListTable.init();

        // Filter function
        function filterHTMLFormDR() {
            diversityListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-DR")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormDR();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-DR").on("click", function (event) {
            document.getElementById("allDiversityReportPdf").style.display="none";
            document.getElementById("diversitybySearchPdfBtn").style.display="block";
            filterHTMLFormDR();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-DR").on("click", function (event) {
            worktypeDR.clear(true);
            departmentDR.clear(true);
            ethnicityDR.clear(true);
            nationalityDR.clear(true);
            genderDR.clear(true);
            $("#startdate-DR").val('');
            $("#enddate-DR").val('');
            $("#status_id-DR").val('1');
            document.getElementById("allDiversityReportPdf").style.display="block";
            document.getElementById("diversitybySearchPdfBtn").style.display="none";

            filterHTMLFormDR();
        });
    }

    $("#diversitybySearchPdfBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-DR").val() != "" ? $("#startdate-DR").val() : "";
        let enddate = $("#enddate-DR").val() != "" ? $("#enddate-DR").val() : "";
        let worktype = $("#employee_work_type_id-diversity").val() != "" ? $("#employee_work_type_id-diversity").val() : "";
        let department = $("#department_id-diversity").val() != "" ? $("#department_id-diversity").val() : "";
        let ethnicity = $("#ethnicity-DR").val() != "" ? $("#ethnicity-DR").val() : "";
        let nationality = $("#nationality-DR").val() != "" ? $("#nationality-DR").val() : "";
        let gender = $("#gender-DR").val() != "" ? $("#gender-DR").val() : "";
        let status = $("#status_id-DR").val() != "" ? $("#status_id-DR").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.diversitybysearch.pdf"),
            params: {
                startdate: startdate, worktype:worktype, department:department, ethnicity:ethnicity, nationality:nationality, gender:gender, enddate:enddate, status:status
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            responseType: 'blob',
        })
        .then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Diversity Information.pdf');
                document.body.appendChild(link);
                link.click();
                
        })
        .catch((error) => {
            console.log(error);
        });
    });
})();