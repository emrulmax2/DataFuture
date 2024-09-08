import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import moment from 'moment';


(function(){

    if($('#personalTutorDashboard').length > 0){
        $("#load-more").on('click',function(e){
            e.preventDefault()
            $('.more').removeClass('hidden');
            $("#load-more").hide()
        });
    }

    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    const start_date = new Litepicker({
        element: document.getElementById('personalTutorCalendar'),
        ...dateOption,
        setup: (picker) => {
            picker.on('selected', (date) => {
                let personalTutorId  = $('#personalTutorCalendar').attr('data-pt')
                let plan_date =  moment(date.dateInstance).format('DD-MM-YYYY');
                
                axios({
                    method: "post",
                    url: route('pt.get.classes'),
                    data: {personalTutorId : personalTutorId, plan_date : plan_date},
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                }).then((response) => {
                    if (response.status == 200) {
                        $('#todaysClassListWrap').html(response.data.res);

                        setTimeout(function(){
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                        })
                    }
                }).catch((error) => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            });
        }
    });

    let currentRequest = null;
    $('#registration_no').on('keyup paste change', function(){
        var $theInput = $(this);
        var SearchVal = $theInput.val();

        if(SearchVal.length >= 3){
            $('#viewStudentBtn').find('.svgSearch').css({opacity: 0});
            $('#viewStudentBtn').find('.svgLoader').css({opacity: 1});
            currentRequest = $.ajax({
                type: 'POST',
                data: {SearchVal : SearchVal},
                url: route("pt.student.filter.id"),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                beforeSend : function()    {           
                    if(currentRequest != null) {
                        currentRequest.abort();
                        $('#viewStudentBtn').find('.svgSearch').css({opacity: 0});
                        $('#viewStudentBtn').find('.svgLoader').css({opacity: 1});
                    }
                },
                success: function(data) {
                    $('#viewStudentBtn').find('.svgSearch').css({opacity: 1});
                    $('#viewStudentBtn').find('.svgLoader').css({opacity: 0});
                    $theInput.siblings('.autoFillDropdown').html(data.htm).fadeIn();
                    $theInput.siblings('#profileUrl').val('');
                    $theInput.parent('.autoCompleteField').siblings('#viewStudentBtn').attr('disabled', 'disabled');
                },
                error:function(e){
                    console.log('Error');
                    $('#viewStudentBtn').find('.svgSearch').css({opacity: 1});
                    $('#viewStudentBtn').find('.svgLoader').css({opacity: 0});
                    $theInput.siblings('.autoFillDropdown').html('').fadeOut();
                    $theInput.siblings('#profileUrl').val('');
                    $theInput.parent('.autoCompleteField').siblings('#viewStudentBtn').attr('disabled', 'disabled');
                }
            });
        }else{
            $('#viewStudentBtn').find('.svgSearch').css({opacity: 1});
            $('#viewStudentBtn').find('.svgLoader').css({opacity: 0});
            $theInput.siblings('.autoFillDropdown').html('').fadeOut();
            $theInput.siblings('#profileUrl').val('');
            $theInput.parent('.autoCompleteField').siblings('#viewStudentBtn').attr('disabled', 'disabled');

            if(currentRequest != null) {
                currentRequest.abort();
            }
        }
    });

    $('.autoFillDropdown').on('click', 'li a:not(".disable")', function(e){
        e.preventDefault();
        var profile_url = $(this).attr('href');
        var label = $(this).attr('data-label');
        window.location.href = profile_url;

        /*$(this).parent('li').parent('ul.autoFillDropdown').siblings('.registration_no').val(label);
        $(this).parent('li').parent('ul.autoFillDropdown').siblings('#profileUrl').val(profile_url);
        $(this).parent('li').parent('ul.autoFillDropdown').parent('.autoCompleteField').siblings('#viewStudentBtn').removeAttr('disabled');
        $(this).parent('li').parent('.autoFillDropdown').html('').fadeOut();*/
    });

    /*$('#viewStudentBtn').on('click', function(e){
        e.preventDefault();
        var profileUrl = $('#profileUrl').val();
        if(profileUrl != ''){
            window.location.href = profileUrl;
        }else{
            $('#viewStudentBtn').attr('disabled', 'disabled');
        }
    })*/
    
    //const termDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#term-dropdown"));
    /*$('.term-select').on('click', function (e) {
        e.preventDefault();
        let tthis = $(this)
        let btnSvg = $("#selected-term svg")
        let selectedText = $("#selected-term span")
        let selectedButtonText = $("#selectedTermButton button")
        let totalModule = $("#totalModule")
        let termname = tthis.text()
        let instanceTermId = tthis.data('instance_term_id')
        let tutorId = tthis.data('tutor_id')
        btnSvg.eq(0).hide()
        btnSvg.eq(1).show()
        axios({
            method: "get",
            url: route("tutor-dashboard.tutor.modulelist",[instanceTermId,tutorId]),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                selectedText.html(termname)
                selectedButtonText.html(termname)
                totalModule.html(dataset.current_term[instanceTermId].total_modules)
                
                $(".term-select").removeClass('dropdown-active')
                $("#term-"+instanceTermId).addClass('dropdown-active')
                termDropdown.hide()
                btnSvg.eq(1).hide()
                btnSvg.eq(0).show()
                
                    let html = "";
                    $(dataset.module_data[instanceTermId]).each(function(index, dataSet){
                        
                        
                        $(dataSet).each(function(index, data){
                            console.log(data.id)
                            html +=`<a href="${ route('tutor-dashboard.plan.module.show',data.id) }" target="_blank" style="inline-block">
                                    <div id="moduleset-${data.id}" class="intro-y module-details_${data.id} ">
                                        <div class="box px-4 py-4 mb-3 flex items-center zoom-in">
                                            <div class="ml-4 mr-auto">
                                                <div class="font-medium">${ data.module }</div>
                                                <div class="text-slate-500 text-xs mt-0.5">${ data.course }</div>
                                            </div>
                                            <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-12 h-10 inline-flex justify-center items-center">${ data.group }</div>
                                        </div>
                                    </div>
                                </a>`

                        })
                    })
                    
                    $("#personalTutormoduleList").html(html)
                    

            }
        }).catch((error) => {
            btnSvg.eq(1).hide()
            btnSvg.eq(0).show()
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addSmtpForm .${key}`).addClass('border-danger')
                        $(`#addSmtpForm  .error-${key}`).html(val)
                    }
                }else if(error.response.status == 303){

                    document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                        $("#confirmModal .confModTitle").html("End Class!");
                        $("#confirmModal .confModDesc").html('Do you want to End Class.');
                    });   
                    confirmModal.show();
                    editPunchNumberDeteilsModal.hide();

                } else {
                    console.log('error');
                }
            }
        });
        
    });*/
    
})();