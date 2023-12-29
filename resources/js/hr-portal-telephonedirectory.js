import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");

var telephonedirectoryTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let startdate = $("#startdate-telephonedirectory").val() != "" ? $("#startdate-telephonedirectory").val() : "";
        let enddate = $("#enddate-telephonedirectory").val() != "" ? $("#enddate-telephonedirectory").val() : "";
        let worktype = $("#employee_work_type_id-telephonedirectory").val() != "" ? $("#employee_work_type_id-telephonedirectory").val() : "";
        let department = $("#department_id-telephonedirectory").val() != "" ? $("#department_id-telephonedirectory").val() : "";
        let ethnicity = $("#ethnicity-telephonedirectory").val() != "" ? $("#ethnicity-telephonedirectory").val() : "";
        let nationality = $("#nationality-telephonedirectory").val() != "" ? $("#nationality-telephonedirectory").val() : "";
        let gender = $("#gender-telephonedirectory").val() != "" ? $("#gender-telephonedirectory").val() : "";
        let status = $("#status_id-telephonedirectory").val() != "" ? $("#status_id-telephonedirectory").val() : "";

        let tableContent = new Tabulator("#telephonedirectoryTable", {
            ajaxURL: route("hr.portal.reports.telephonedirectory.list"),
            ajaxParams: { ethnicity:ethnicity, nationality:nationality, startdate:startdate, worktype:worktype, department:department, gender:gender, enddate:enddate, status:status },
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
            
            columns:[
                {
                    title:"", field:"firstcha",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="font-bold whitespace-normal text-sm">'+cell.getData().firstcha+'</div>';
                    }
                },
            ],
            rowFormatter:function(row){
                //create and style holder elements
                var holderEl = document.createElement("div");
                var tableEl  = document.createElement("div");
        
                holderEl.appendChild(tableEl);
        
                row.getElement().appendChild(holderEl);

                tableEl.setAttribute("id","tableEl");
        
                var subTable = new Tabulator(tableEl, {
                    layout:"fitColumns",
                    data:row.getData().dataArray,
                    columns:[
                        {title:"Name", field:"name", headerHozAlign: "left"},
                        {title:"Telephone", field:"telephone", headerHozAlign: "left"},
                        {title:"Mobile", field:"mobile", headerHozAlign: "left"},
                        {title:"Email", field:"email", headerHozAlign: "left"},
                    ],
                    renderComplete() {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    },
                })
            },
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
            return confirm( values.length > 1 ? "Are you sure you want to remove this " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    $("#tabulator-html-filter-go-TD").on("click", function (event) { 
        event.preventDefault();

        var startdateTD = document.getElementById("startdate-telephonedirectory").value;
        var worktypeTD = document.getElementById("employee_work_type_id-telephonedirectory").value;
        var departmentTD = document.getElementById("department_id-telephonedirectory").value;
        var ethnicityTD = document.getElementById("ethnicity-telephonedirectory").value;
        var nationalityTD = document.getElementById("nationality-telephonedirectory").value;
        var genderTD = document.getElementById("gender-telephonedirectory").value;
        var enddateTD = document.getElementById("enddate-telephonedirectory").value;
        var statusTD = document.getElementById("status_id-telephonedirectory").value;

        if(startdateTD !="" || worktypeTD !="" || departmentTD !="" || ethnicityTD !="" || nationalityTD !="" || genderTD !="" || enddateTD !="" || statusTD !=1) {           
            telephonedirectoryTable.init();
            document.getElementById("telephonedirectorybySearchExcelBtn").style.display="block";
            document.getElementById("telephonedirectorybySearchPdfBtn").style.display="block";
            document.getElementById("allTelephoneDirectoryExcelBtn").style.display="none";
            document.getElementById("allTelephoneDirectoryPdfBtn").style.display="none";
        } else {
            telephonedirectoryTable.init();
            document.getElementById("telephonedirectorybySearchExcelBtn").style.display="none";
            document.getElementById("telephonedirectorybySearchPdfBtn").style.display="none";
            document.getElementById("allTelephoneDirectoryExcelBtn").style.display="block";
            document.getElementById("allTelephoneDirectoryPdfBtn").style.display="block";
        }

        // Filter function
        function filterHTMLFormTD() {
            telephonedirectoryTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-TD")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormTD();
                }
            }
        );
    });

    // On reset filter form
    $("#tabulator-html-filter-reset-TD").on("click", function (event) {
        $("#employee_work_type_id-telephonedirectory").val('');
        $("#department_id-telephonedirectory").val('');
        $("#ethnicity-telephonedirectory").val('');
        $("#nationality-telephonedirectory").val('');
        $("#gender-telephonedirectory").val('');
        $("#startdate-telephonedirectory").val('');
        $("#enddate-telephonedirectory").val('');
        $("#status_id-telephonedirectory").val('1');
        telephonedirectoryTable.init();
        document.getElementById("allTelephoneDirectoryExcelBtn").style.display="block";
        document.getElementById("allTelephoneDirectoryPdfBtn").style.display="block";
        document.getElementById("telephonedirectorybySearchExcelBtn").style.display="none";
        document.getElementById("telephonedirectorybySearchPdfBtn").style.display="none";
    });

    $("#telephonedirectorybySearchExcelBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-telephonedirectory").val() != "" ? $("#startdate-telephonedirectory").val() : "";
        let enddate = $("#enddate-telephonedirectory").val() != "" ? $("#enddate-telephonedirectory").val() : "";
        let worktype = $("#employee_work_type_id-telephonedirectory").val() != "" ? $("#employee_work_type_id-telephonedirectory").val() : "";
        let department = $("#department_id-telephonedirectory").val() != "" ? $("#department_id-telephonedirectory").val() : "";
        let ethnicity = $("#ethnicity-telephonedirectory").val() != "" ? $("#ethnicity-telephonedirectory").val() : "";
        let nationality = $("#nationality-telephonedirectory").val() != "" ? $("#nationality-telephonedirectory").val() : "";
        let gender = $("#gender-telephonedirectory").val() != "" ? $("#gender-telephonedirectory").val() : "";
        let status = $("#status_id-telephonedirectory").val() != "" ? $("#status_id-telephonedirectory").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.telephonedirectorybysearch.excel"),
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
            link.setAttribute('download', 'Employee_Telephone_Directory.xlsx'); 
            document.body.appendChild(link);
            link.click();              
        })
        .catch((error) => {
            console.log(error);
        });
    });

    $("#telephonedirectorybySearchPdfBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-telephonedirectory").val() != "" ? $("#startdate-telephonedirectory").val() : "";
        let enddate = $("#enddate-telephonedirectory").val() != "" ? $("#enddate-telephonedirectory").val() : "";
        let worktype = $("#employee_work_type_id-telephonedirectory").val() != "" ? $("#employee_work_type_id-telephonedirectory").val() : "";
        let department = $("#department_id-telephonedirectory").val() != "" ? $("#department_id-telephonedirectory").val() : "";
        let ethnicity = $("#ethnicity-telephonedirectory").val() != "" ? $("#ethnicity-telephonedirectory").val() : "";
        let nationality = $("#nationality-telephonedirectory").val() != "" ? $("#nationality-telephonedirectory").val() : "";
        let gender = $("#gender-telephonedirectory").val() != "" ? $("#gender-telephonedirectory").val() : "";
        let status = $("#status_id-telephonedirectory").val() != "" ? $("#status_id-telephonedirectory").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.telephonedirectorybysearch.pdf"),
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
            link.setAttribute('download', 'Employee_Telephone_Directory.pdf');
            document.body.appendChild(link);
            link.click();              
        })
        .catch((error) => {
            console.log(error);
        });
    });
})();