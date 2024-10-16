import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){
    let awardTomOptions = {
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

    let awardTomOptionsMul = {
        ...awardTomOptions,
        plugins: {
            ...awardTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var award_semester_id = new TomSelect('#award_semester_id', awardTomOptionsMul);
    $('#award_semester_id').on('change', function(){
        $('#printPdfAwardRateBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#awardRateWrap').fadeOut().html('');
    });

    $('#awardRateSearchForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('awardRateSearchForm');
        let award_semester_id = $form.find('#award_semester_id').val();
        
        if(award_semester_id.length > 0){
            $form.find('.error-award_semester_id').html('')
            document.querySelector('#awardRateBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#awardRateBtn svg").style.cssText ="display: inline-block;";
            $('#printPdfAwardRateBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#awardRateWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.intake.performance.get.award.rate.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#awardRateBtn').removeAttribute('disabled');
                document.querySelector("#awardRateBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    //console.log(response.data);
                    //return false;
                    let pdf_url = route('reports.intake.performance.print.award.rate.report', award_semester_id.join('_'));
                    $('#awardRateWrap').fadeIn().html(response.data.htm);
                    $('#printPdfAwardRateBtn').attr('href', pdf_url).fadeIn();

                    setTimeout(() => {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#awardRateBtn').removeAttribute('disabled');
                document.querySelector("#awardRateBtn svg").style.cssText = "display: none;";
                $('#printPdfAwardRateBtn').attr('href', 'javascript:void(0);').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-award_semester_id').html('Semesters can not be empty.');
            $('#awardRateWrap').fadeOut().html('');
            $('#printPdfAwardRateBtn').attr('href', 'javascript:void(0);').fadeOut();
        }
    });

    $('#awardRateWrap').on('click', '.semesterToggle', function(e){
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

    $('#awardRateWrap').on('click', '.courseToggle', function(e){
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

})();