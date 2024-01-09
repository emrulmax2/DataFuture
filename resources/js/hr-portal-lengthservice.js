import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");

var lengthserviceTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let startdate = $("#startdate-lengthservice").val() != "" ? $("#startdate-lengthservice").val() : "";
        let enddate = $("#enddate-lengthservice").val() != "" ? $("#enddate-lengthservice").val() : "";
        let worktype = $("#employee_work_type_id-lengthservice").val() != "" ? $("#employee_work_type_id-lengthservice").val() : "";
        let department = $("#department_id-lengthservice").val() != "" ? $("#department_id-lengthservice").val() : "";
        let ethnicity = $("#ethnicity-lengthservice").val() != "" ? $("#ethnicity-lengthservice").val() : "";
        let nationality = $("#nationality-lengthservice").val() != "" ? $("#nationality-lengthservice").val() : "";
        let gender = $("#gender-lengthservice").val() != "" ? $("#gender-lengthservice").val() : "";
        let status = $("#status_id-lengthservice").val() != "" ? $("#status_id-lengthservice").val() : "";

        let tableContent = new Tabulator("#lengthserviceTable", {
            ajaxURL: route("hr.portal.reports.lengthservice.list"),
            ajaxParams: { ethnicity:ethnicity, nationality:nationality, startdate:startdate, worktype:worktype, department:department, gender:gender, enddate:enddate, status:status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: true,
            paginationSizeSelector: false,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            
            columns:[
                {
                    title:"", field:"year",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="font-bold whitespace-normal text-sm">'+cell.getData().year+' Years</div>';
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
                        {title:"Name", field:"name", headerSort: false, headerHozAlign: "left"},
                        {title:"Started On", field:"started_on", headerSort: false, headerHozAlign: "left"},
                        {title:"Ended On", field:"ended_on", headerSort: false, headerHozAlign: "left"},
                        {title:"Length of Service", field:"length", headerSort: false, headerHozAlign: "left"},
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
            renderStarted:function(){
                $("#lengthserviceTable .tabulator-footer").hide();
                $(".tabulator-headers").css('height',0);  
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

    $("#tabulator-html-filter-go-LS").on("click", function (event) { 
        event.preventDefault();

        var startdateLS = document.getElementById("startdate-lengthservice").value;
        var worktypeLS = document.getElementById("employee_work_type_id-lengthservice").value;
        var departmentLS = document.getElementById("department_id-lengthservice").value;
        var ethnicityLS = document.getElementById("ethnicity-lengthservice").value;
        var nationalityLS = document.getElementById("nationality-lengthservice").value;
        var genderLS = document.getElementById("gender-lengthservice").value;
        var enddateLS = document.getElementById("enddate-lengthservice").value;
        var statusLS = document.getElementById("status_id-lengthservice").value;

        if(startdateLS !="" || worktypeLS !="" || departmentLS !="" || ethnicityLS !="" || nationalityLS !="" || genderLS !="" || enddateLS !="" || statusLS !=1) {
            lengthserviceTable.init();         
            document.getElementById("lengthservicebySearchExcelBtn").style.display="block";
            document.getElementById("lengthservicebySearchPdfBtn").style.display="block";
            document.getElementById("allLengthServiceExcelBtn").style.display="none";
            document.getElementById("allLengthServicePdfBtn").style.display="none";
        } else {
            lengthserviceTable.init();         
            document.getElementById("lengthservicebySearchExcelBtn").style.display="none";
            document.getElementById("lengthservicebySearchPdfBtn").style.display="none";
            document.getElementById("allLengthServiceExcelBtn").style.display="block";
            document.getElementById("allLengthServicePdfBtn").style.display="block";
        }
        // Filter function
        function filterHTMLFormLS() {
            lengthserviceTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-LS")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormLS();
                }
            }
        );
    });

    // On reset filter form
    $("#tabulator-html-filter-reset-LS").on("click", function (event) {
        $("#startdate-lengthservice").val('');
        $("#enddate-lengthservice").val('');
        let employeeWork = document.getElementById('employee_work_type_id-lengthservice');
        employeeWork.tomselect.setValue("");
        
        let departmentId = document.getElementById('department_id-lengthservice');
        departmentId.tomselect.setValue("");
        
        let ethnicity = document.getElementById('ethnicity-lengthservice');
        ethnicity.tomselect.setValue("");

        let nationality = document.getElementById('nationality-lengthservice');
        nationality.tomselect.setValue("");
        
        let gender = document.getElementById('gender-lengthservice');
        gender.tomselect.setValue("");

        let statusIdContact = document.getElementById('status_id-lengthservice');
        statusIdContact.tomselect.setValue("1");

        lengthserviceTable.init();
        document.getElementById("allLengthServiceExcelBtn").style.display="block";
        document.getElementById("allLengthServicePdfBtn").style.display="block";
        document.getElementById("lengthservicebySearchExcelBtn").style.display="none";
        document.getElementById("lengthservicebySearchPdfBtn").style.display="none";
    });

    $("#lengthservicebySearchExcelBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-lengthservice").val() != "" ? $("#startdate-lengthservice").val() : "";
        let enddate = $("#enddate-lengthservice").val() != "" ? $("#enddate-lengthservice").val() : "";
        let worktype = $("#employee_work_type_id-lengthservice").val() != "" ? $("#employee_work_type_id-lengthservice").val() : "";
        let department = $("#department_id-lengthservice").val() != "" ? $("#department_id-lengthservice").val() : "";
        let ethnicity = $("#ethnicity-lengthservice").val() != "" ? $("#ethnicity-lengthservice").val() : "";
        let nationality = $("#nationality-lengthservice").val() != "" ? $("#nationality-lengthservice").val() : "";
        let gender = $("#gender-lengthservice").val() != "" ? $("#gender-lengthservice").val() : "";
        let status = $("#status_id-lengthservice").val() != "" ? $("#status_id-lengthservice").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.lengthservicebysearch.excel"),
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
            link.setAttribute('download', 'Employee_Service_Lengths.xlsx'); 
            document.body.appendChild(link);
            link.click();              
        })
        .catch((error) => {
            console.log(error);
        });
    });

    $("#lengthservicebySearchPdfBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-lengthservice").val() != "" ? $("#startdate-lengthservice").val() : "";
        let enddate = $("#enddate-lengthservice").val() != "" ? $("#enddate-lengthservice").val() : "";
        let worktype = $("#employee_work_type_id-lengthservice").val() != "" ? $("#employee_work_type_id-lengthservice").val() : "";
        let department = $("#department_id-lengthservice").val() != "" ? $("#department_id-lengthservice").val() : "";
        let ethnicity = $("#ethnicity-lengthservice").val() != "" ? $("#ethnicity-lengthservice").val() : "";
        let nationality = $("#nationality-lengthservice").val() != "" ? $("#nationality-lengthservice").val() : "";
        let gender = $("#gender-lengthservice").val() != "" ? $("#gender-lengthservice").val() : "";
        let status = $("#status_id-lengthservice").val() != "" ? $("#status_id-lengthservice").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.lengthservicebysearch.pdf"),
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
            link.setAttribute('download', 'Employee_Service_Lengths.pdf');
            document.body.appendChild(link);
            link.click();              
        })
        .catch((error) => {
            console.log(error);
        });
    });
})();