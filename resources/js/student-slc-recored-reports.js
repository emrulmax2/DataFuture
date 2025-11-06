import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import tippy, { roundArrow } from "tippy.js";


(function(){
    let srrTomOptions = {
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

    let srrTomOptionsMul = {
        ...srrTomOptions,
        plugins: {
            ...srrTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var srr_semester_id = new TomSelect('#srr_semester_id', srrTomOptionsMul);
    $('#srr_semester_id').on('change', function(){
        $('#printPdfslcRecoredReportBtn, #exportXlslcRecoredReportBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#slcRecoredReportWrap').fadeOut().html('');
    });

    $('#slcRecoredReportForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('slcRecoredReportForm');
        let srr_semester_id = $form.find('#srr_semester_id').val();
        
        if(srr_semester_id.length > 0){
            $form.find('.error-srr_semester_id').html('')
            document.querySelector('#slcRecoredReportBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#slcRecoredReportBtn svg").style.cssText ="display: inline-block;";
            $('#printPdfslcRecoredReportBtn, #exportXlslcRecoredReportBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#slcRecoredReportWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "POST",
                url: route('reports.slc.record.generate.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#slcRecoredReportBtn').removeAttribute('disabled');
                document.querySelector("#slcRecoredReportBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    let pdf_url = route('reports.slc.record.print.report', srr_semester_id.join('_'));
                    let excel_url = route('reports.slc.record.export.report', srr_semester_id.join('_'));
                    $('#slcRecoredReportWrap').fadeIn().html(response.data.htm);
                    $('#printPdfslcRecoredReportBtn').attr('href', pdf_url).fadeIn();
                    $('#exportXlslcRecoredReportBtn').attr('href', excel_url).fadeIn();

                    setTimeout(() => {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#slcRecoredReportBtn').removeAttribute('disabled');
                document.querySelector("#slcRecoredReportBtn svg").style.cssText = "display: none;";
                $('#printPdfslcRecoredReportBtn, #exportXlslcRecoredReportBtn').attr('href', 'javascript:void(0);').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-srr_semester_id').html('Semesters can not be empty.');
            $('#slcRecoredReportWrap').fadeOut().html('');
            $('#printPdfslcRecoredReportBtn, #exportXlslcRecoredReportBtn').attr('href', 'javascript:void(0);').fadeOut();
        }
    });

    
    $('#slcRecoredReportWrap').on('click', '.exportStdList', function(e){
        e.preventDefault();
        let $thebtn = $(this);
        let student_ids = $thebtn.attr('data-ids');

        if(typeof student_ids !== 'undefined' && student_ids !== false && student_ids != ''){
            $thebtn.find('.theLoader').fadeIn();
            axios({
                method: "POST",
                url: route('reports.slc.record.export.student.list'),
                data: {student_ids : student_ids},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                responseType: 'blob',
            }).then(response => {
                $thebtn.find('.theLoader').fadeOut();
                if (response.status == 200) {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'Slc_record_student_lists.xlsx');
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                }
            }).catch(error => {
                $thebtn.find('.theLoader').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }
    })

    $('#slcRecoredReportWrap').on('click', '.semesterToggle', function(e){
        e.preventDefault();
        var semesterid = $(this).attr('data-semesterid');
        if($(this).hasClass('active')){
            $('.semesterTermRow_'+semesterid).fadeOut();
            $('.semesterCourseRow_'+semesterid).fadeOut();
            $('.semesterCourseRow_'+semesterid).find('.courseToggle').removeClass('active');
            $(this).removeClass('active');
        }else{
            $('.semesterCourseRow_'+semesterid).fadeIn();
            $(this).addClass('active');
        }
    });
})()