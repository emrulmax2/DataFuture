import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import tippy, { roundArrow } from "tippy.js";

("use strict");
var submissionPerformanceSTDListTable = (function () {
    var _tableGen = function (student_ids) {
        let tableContent = new Tabulator("#submissionPerformanceSTDListTable", {
            ajaxURL: route("reportsterm.performance.submission.student.list"),
            ajaxParams: { student_ids : student_ids },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 50,100,200,500],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Reg. No",
                    field: "registration_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                                    html += '<img alt="'+cell.getData().first_name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -13px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().registration_no+'</div>';
                                    
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "First Name",
                    field: "first_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Name",
                    field: "last_name",
                    headerHozAlign: "left",
                },
                {
                    title: "",
                    field: "full_time",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {  
                        let day=false;
                        if(cell.getData().full_time==1) 
                            day = 'text-slate-900' 
                        else  
                            day = 'text-amber-600'
                        var html = '<div class="flex">';
                                if(cell.getData().flag_html != ''){
                                    html += cell.getData().flag_html;
                                }
                                if(cell.getData().due > 1){
                                    html += '<div class="mr-2 '+(cell.getData().due == 2 ? 'text-success' : (cell.getData().due == 3 ? 'text-warning' : 'text-danger'))+'"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="badge-pound-sterling" class="lucide lucide-badge-pound-sterling w-6 h-6"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"></path><path d="M8 12h4"></path><path d="M10 16V9.5a2.5 2.5 0 0 1 5 0"></path><path d="M8 16h7"></path></svg></div>';
                                }
                                html += '<div class="w-8 h-8 '+day+' intro-x inline-flex">';
                                if(cell.getData().full_time==1)
                                    html += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sunset" class="lucide lucide-sunset w-6 h-6"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg>';
                                else
                                    html += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sun" class="lucide lucide-sun w-6 h-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>';
                                
                                html += '</div>';
                            if(cell.getData().disability==1)
                                html += '<div class="inline-flex intro-x " style="color:#9b1313"><i data-lucide="accessibility" class="w-6 h-6"></i></div>';
                            
                            html += '</div>';
                            createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide"});

                        return html;
                    }
                },
                {
                    title: "Semester",
                    field: "semester",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                    width: 180,
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
                
                $(document).find('.autoFillDropdown').html('').fadeOut();
                $(document).find('.flagLinks').each(function(){
                    $(this).attr('href', 'javascript:void(0);');
                })
            },
            rowClick:function(e, row){
                window.open(row.getData().url, '_blank');
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
    };
    return {
        init: function (student_ids) {
            _tableGen(student_ids);
        },
    };
})();

(function(){
    let subPerfTomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let subPerfTomOptionsMul = {
        ...subPerfTomOptions,
        plugins: {
            ...subPerfTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var sub_perf_term_id = new TomSelect('#sub_perf_term_id', subPerfTomOptions);
    $('#sub_perf_term_id').on('change', function(){
        $('#printSubmissionPerformanceReportBtn, #exportSubmissionPerformanceReportBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#submissionPerformanceReportWrap').fadeOut().html('');
    });

    $('#submissionPerformanceReportForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('submissionPerformanceReportForm');
        let sub_perf_term_id = $form.find('#sub_perf_term_id').val();
        
        if(sub_perf_term_id.length > 0){
            $form.find('.error-sub_perf_term_id').html('')
            document.querySelector('#submissionPerformanceReportBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#submissionPerformanceReportBtn svg").style.cssText ="display: inline-block;";
            $('#printSubmissionPerformanceReportBtn, #exportSubmissionPerformanceReportBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#submissionPerformanceReportWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.term.performance.submission.generate.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#submissionPerformanceReportBtn').removeAttribute('disabled');
                document.querySelector("#submissionPerformanceReportBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    //console.log(response.data);
                    //return false;
                    let pdf_url = route('reports.term.performance.submission.print.report', sub_perf_term_id);
                    let excel_url = route('reports.term.performance.submission.report', sub_perf_term_id);
                    $('#submissionPerformanceReportWrap').fadeIn().html(response.data.htm);
                    $('#printSubmissionPerformanceReportBtn').attr('href', pdf_url).fadeIn();
                    $('#exportSubmissionPerformanceReportBtn').attr('href', excel_url).fadeIn();

                    setTimeout(() => {
                        $("#submissionPerformanceReportWrap .tooltip").each(function () {
                            let tTipoptions = {
                                content: $(this).attr("title"),
                            };
                            $(this).removeAttr("title");
                    
                            tippy(this, {
                                arrow: roundArrow,
                                animation: "shift-away",
                                ...tTipoptions,
                            });
                        });
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#submissionPerformanceReportBtn').removeAttribute('disabled');
                document.querySelector("#submissionPerformanceReportBtn svg").style.cssText = "display: none;";
                $('#printSubmissionPerformanceReportBtn, #exportSubmissionPerformanceReportBtn').attr('href', 'javascript:void(0);').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-sub_perf_term_id').html('Semesters can not be empty.');
            $('#submissionPerformanceReportWrap').fadeOut().html('');
            $('#printSubmissionPerformanceReportBtn, #exportSubmissionPerformanceReportBtn').attr('href', 'javascript:void(0);').fadeOut();
        }
    });

    $('#submissionPerformanceReportWrap').on('click', '#firstSubmissionPerformanceReportTable .semesterFirstRowToggle', function(e){
        e.preventDefault();
        var semesterid = $(this).attr('data-semester');
        if($(this).hasClass('active')){
            $('#firstSubmissionPerformanceReportTable .course_first_row_'+semesterid).fadeOut();
            $(this).removeClass('active');
        }else{
            $('#firstSubmissionPerformanceReportTable .course_first_row_'+semesterid).fadeIn();
            $(this).addClass('active');
        }
    });

    $('#submissionPerformanceReportWrap').on('click', '#allSubmissionPerformanceReportTable .semesterAllRowToggle', function(e){
        e.preventDefault();
        var semesterid = $(this).attr('data-semester');
        if($(this).hasClass('active')){
            $('#allSubmissionPerformanceReportTable .course_all_row_'+semesterid).fadeOut();
            $(this).removeClass('active');
        }else{
            $('#allSubmissionPerformanceReportTable .course_all_row_'+semesterid).fadeIn();
            $(this).addClass('active');
        }
    });

    
    const submissionPerformanceSTDListModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#submissionPerformanceSTDListModal"));
    const submissionPerformanceSTDListModalEl = document.getElementById('submissionPerformanceSTDListModal')
    submissionPerformanceSTDListModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#submissionPerformanceSTDListTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
    });
    $('#submissionPerformanceReportWrap').on('click', '.subPerfmStdBtn', function(e){
        e.preventDefault();
        let $thebtn = $(this);
        let student_ids = $thebtn.attr('data-ids');

        if(typeof student_ids !== 'undefined' && student_ids !== false && student_ids != ''){
            submissionPerformanceSTDListModal.show();
            submissionPerformanceSTDListTable.init(student_ids)
        }
    })
})();