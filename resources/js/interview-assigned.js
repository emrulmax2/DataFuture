import xlsx from "xlsx";
import { createElement, createIcons, icons,Minus,Plus } from "lucide";
import Tabulator from "tabulator-tables";
import { constant } from "lodash";

("use strict");

let checkBoxAll = '<div data-tw-merge class="flex items-cente mt-2"><input  id="checkbox-all" value="" data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50" />\
<label data-tw-merge for="checkbox-all" class="cursor-pointer ml-2">Applicant</label>\
</div>' 

const minusIcon = createElement(Minus)
minusIcon.setAttribute('stroke-width', '1.5')

const plusIcon = createElement(Plus)
plusIcon.setAttribute('stroke-width', '1.5')
// our object array
var dataStudents = [];

var interviewListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";


        let tableContent = new Tabulator("#interviewTableId", {
            dataTree:true,
            dataTreeCollapseElement:minusIcon,
            dataTreeExpandElement:plusIcon,
            ajaxURL: route("interview.assigned.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            //ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            //columnDefs: [ { orderable: false, targets: [0,2], }],
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "",
                    field: "",
                    width: "80",
                    headerSort:false,
                },
                {
                    title: "Task Name",
                    field: "taskname",
                    width: "180",
                    headerSort:false,
                },
                {
                    title: checkBoxAll,
                    field: "data",
                    width: "280",
                    headerSort:false,
                    formatter: (cell) => {
                        const value = cell.getValue();
                        
                        if (value) {
                            return `
                            <div data-tw-merge class="applicant-check flex items-center mt-2"><input data-task="${value.task_list_id}" data-id="${value.id}" data-name="${value.name}"  data-reg="${value.register}"  name="studentData[]" data-tw-merge type="checkbox" id="checkbox-switch-${value.id}" value="" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50"  />
                                <label data-tw-merge for="checkbox-switch-${value.id}" class="cursor-pointer ml-2">${value.name}</label>
                            </div>
                            `;
                        }
                    },
                    cellClick:function(e, cell){
                        //e - the click event object
                        //cell - cell component

                        dataStudents = []
                        $('button.interviewer').attr('disabled', 'disabled')  
                            
                        let vData = document.querySelectorAll('input[name="studentData[]"]:checked')
                        //if(vData.length==2) console.log("WORKS");
                        for(let i=0; i < vData.length; i++) {
                            let myStudenObject = {}
                                myStudenObject.id = vData[i].getAttribute('data-id')
                                myStudenObject.name = vData[i].getAttribute('data-name')
                                myStudenObject.register = vData[i].getAttribute('data-reg')
                                myStudenObject.task_list_id = vData[i].getAttribute('data-task')
                                
                                dataStudents.push(myStudenObject)
                          
                        }
                            
                        if(dataStudents.length>0) {
                            $('button.interviewer').removeAttr('disabled')

                        }

                    },
                },
                
                {
                    title: "Gender",
                    field: "gender",
                    headerHozAlign: "left",
                },
                {
                    title: "Applicant No.",
                    field: "col",
                    headerHozAlign: "left",
                },
                {
                    title: "Assigned Interviewer",
                    field: "interviewer",
                    headerHozAlign: "left",
                },
                
                {
                    title: "Assigned Date",
                    field: "assignedDate",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });               

                $("#checkbox-all").on("click",function(event){
                    
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
                sheetName: "Semester Details",
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
    $('#interviewerSelectForm').find('.assign__input').removeClass('border-danger')
    $('#interviewerSelectForm').find('.assign__input-error').html('')

    if ($("#interviewTableId").length) {
        // Init Table
        interviewListTable.init();
        $('button.interviewer').attr('disabled', 'disabled')  
        
        // Filter function
        function filterHTMLForm() {
            interviewListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm")[0].addEventListener(
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
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("");
            filterHTMLForm();
        });
        
        $('#checkbox-all').on("click", function (e) { 
            if(this.checked) {
                $('input[name="studentData[]"]').prop("checked", true).trigger("change");
            
                dataStudents = []
                             
                $('button.interviewer').attr('disabled', 'disabled')  
                    
                let vData = document.querySelectorAll('input[name="studentData[]"]:checked')
                //if(vData.length==2) console.log("WORKS");
                for(let i=0; i < vData.length; i++) {
                    let myStudenObject = {}
                        myStudenObject.id = vData[i].getAttribute('data-id')
                        myStudenObject.name = vData[i].getAttribute('data-name')
                        myStudenObject.register = vData[i].getAttribute('data-reg')
                        myStudenObject.task_list_id = vData[i].getAttribute('data-task')
                        
                        dataStudents.push(myStudenObject)
                  
                }
                const ids = dataStudents.map(({ id }) => id);
                const filtered = dataStudents.filter(({ id }, index) => !ids.includes(id, index + 1));
                dataStudents = filtered;
                    
                if(dataStudents.length>0) {
                    $('button.interviewer').removeAttr('disabled')

                }
            } else {    
                $('input[name="studentData[]"]').prop("checked", false).trigger("change");
                $('button.interviewer').attr('disabled', 'disabled');
                
            }
        });
        $('button.interviewer').on("click", function (event) {
            
            const ids = dataStudents.map(({ id }) => id);
            const filtered = dataStudents.filter(({ id }, index) => !ids.includes(id, index + 1));
            dataStudents = filtered;
            let html="";
            let intervieweeListTable = document.getElementById("intervieweelist");
            intervieweeListTable.innerHTML =""
            
            for(let i=0; i<filtered.length;i++) {
                
                html += '<tr  class="text-center">\
                <td class="p-2 border border-cyan-800">'+filtered[i].id+'</th>\
                <th class="p-2 border border-cyan-800">'+filtered[i].name+'</th>\
                <th class="p-2 border border-cyan-800">'+filtered[i].register+'</th>\
            </tr>'
            }
            intervieweeListTable.innerHTML = html
        });
        $('#interviewerSelectForm').on("submit", function (e) {

            $('#interviewerSelectForm').find('.user__input').removeClass('border-danger')
            $('#interviewerSelectForm').find('.user__input-error').html('')

            e.preventDefault()
            const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#selectInterviewModal"));
            const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
            let getIds = []
            
            for(let i=0; i<dataStudents.length;i++) {

                getIds.push(dataStudents[i].id)

            }
            const ids = document.getElementById("ids")
            ids.value = getIds.join(',').toString()
            document.querySelector('#assign').setAttribute('disabled', 'disabled')
            document.querySelector("#assign svg").style.cssText ="display: inline-block;"

            //const form = document.getElementById('interviewerSelectForm')
            //let form_data = new FormData(form);
            const user = document.getElementById('user').value;
            
            axios({
                method: "post",
                url: route('interviewlist.assign.update'),
                data: {data: dataStudents,user: user},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {

                document.querySelector('#assign').removeAttribute('disabled');
                document.querySelector("#assign svg").style.cssText = "display: none;";
                console.log(response);
                if (response.status == 200) {
                    document.querySelector('#assign').removeAttribute('disabled');
                    document.querySelector("#assign svg").style.cssText = "display: none;";
                    $('.user__input').val('');
                    addModal.hide();
                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(
                                "Success!"
                            );
                            $("#successModal .successModalDesc").html('Data Inserted');
                        });                
                        
                }
                interviewListTable.init();
            }).catch(error => {
                document.querySelector('#assign').removeAttribute('disabled');
                document.querySelector("#assign svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                        $('#interviewerSelectForm #user').val('');
                    } else {
                        console.log('error');
                    }
                }
            });


        });
        
    }
})()