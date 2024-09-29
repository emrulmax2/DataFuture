import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){
    let dueTomOptions = {
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

    let dueTomOptionsMul = {
        ...dueTomOptions,
        plugins: {
            ...dueTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var atn_semester_id = new TomSelect('#atn_semester_id', dueTomOptionsMul);
    $('#atn_semester_id').on('change', function(){
        $('#printPdfAtnRateBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#attendanceRateWrap').fadeOut().html('');
    });

    $('#attendanceRateSearchForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('attendanceRateSearchForm');
        let atn_semester_id = $form.find('#atn_semester_id').val();
        
        if(atn_semester_id.length > 0){
            $form.find('.error-atn_semester_id').html('')
            document.querySelector('#IntakeAttnRateBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#IntakeAttnRateBtn svg").style.cssText ="display: inline-block;";
            $('#printPdfAtnRateBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#attendanceRateWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.intake.performance.get.attendance.rate'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#IntakeAttnRateBtn').removeAttribute('disabled');
                document.querySelector("#IntakeAttnRateBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    let pdf_url = route('reports.intake.performance.print.attendance.rate', atn_semester_id.join('_'));
                    $('#attendanceRateWrap').fadeIn().html(response.data.htm);
                    $('#printPdfAtnRateBtn').attr('href', pdf_url).fadeIn();

                    setTimeout(() => {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#IntakeAttnRateBtn').removeAttribute('disabled');
                document.querySelector("#IntakeAttnRateBtn svg").style.cssText = "display: none;";
                $('#printPdfAtnRateBtn').attr('href', 'javascript:void(0);').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-atn_semester_id').html('Semesters can not be empty.');
            $('#attendanceRateWrap').fadeOut().html('');
            $('#printPdfAtnRateBtn').attr('href', 'javascript:void(0);').fadeOut();
        }
    });

    
    $('#attendanceRateWrap').on('click', '.semesterToggle', function(e){
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

    $('#attendanceRateWrap').on('click', '.courseToggle', function(e){
        e.preventDefault();
        var semesterid = $(this).attr('data-semesterid');
        var courseid = $(this).attr('data-courseid');
        if($(this).hasClass('active')){
            $('.termRow_'+semesterid+'_'+courseid).fadeOut();
            $(this).removeClass('active');
        }else{
            $('.termRow_'+semesterid+'_'+courseid).fadeIn();
            $(this).addClass('active');
        }
    })
    

})()