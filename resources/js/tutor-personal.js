
(function(){

    if($('#personalTutorDashboard').length > 0){
        $("#load-more").on('click',function(e){
            e.preventDefault()
            $('.more').removeClass('hidden');
            $("#load-more").hide()
        })
    }
    
    //const termDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#term-dropdown"));
    $('.term-select').on('click', function (e) {
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
        
    });
    
})();