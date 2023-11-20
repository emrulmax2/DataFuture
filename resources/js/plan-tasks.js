import IMask from 'imask';
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import { createElement, Plus,Minus } from 'lucide';
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Dropzone from "dropzone";


(function(){

    
    /* Start Dropzone */
    if($("#addStudentPhotoModal").length > 0){
        let dzErrors = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.addStudentPhotoForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 5,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            addRemoveLinks: true,
            //thumbnailWidth: 100,
            //thumbnailHeight: 100,
        };

        let options = {
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
        };


        var drzn1 = new Dropzone('#addStudentPhotoForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#addStudentPhotoModal .modal-content .uploadError').remove();
            $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#addStudentPhotoModal .modal-content .uploadError').remove();
            }, 4000)
        });

        drzn1.on("error", function(file, response){
            dzErrors = true;
        });

        drzn1.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on("complete", function(file) {
            //drzn1.removeFile(file);
        }); 

        drzn1.on('queuecomplete', function(){
            $('#uploadStudentPhotoBtn').removeAttr('disabled');
            document.querySelector("#uploadStudentPhotoBtn svg").style.cssText ="display: none;";

            if(!dzErrors){
                drzn1.removeAllFiles();

                $('#addStudentPhotoModal .modal-content .uploadError').remove();
                $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> WOW! Student photo successfully uploaded.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#addStudentPhotoModal .modal-content .uploadError').remove();
                    window.location.reload();
                }, 3000);
            }else{
                $('#addStudentPhotoModal .modal-content .uploadError').remove();
                $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try later.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                
                setTimeout(function(){
                    $('#addStudentPhotoModal .modal-content .uploadError').remove();
                }, 5000);
            }
        })

        $('#uploadStudentPhotoBtn').on('click', function(e){
            e.preventDefault();
        
            document.querySelector('#uploadStudentPhotoBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadStudentPhotoBtn span").style.cssText ="display: inline-block;";
            
            drzn1.processQueue();
            
        });
        $('.task-upload__Button').on('click', function(e){
            let tthis = $(this);
            let planTaskId = tthis.data('plantaskid');
            $("input[name='plan_task_id']").val(planTaskId);

        });
        
    }
    /* End Dropzone */

    /**
     * Accordian Button (+/-) Works
     */
        $('.accordion-button').on('click',function(){
            let tthis = $(this)

            const plusIcon = createElement(Plus); // Returns HTMLElement (svg)
            const minusIcon = createElement(Minus); // Returns HTMLElement (svg)
            //console.log(plusIcon)
            // set custom attributes with browser native functions
            
            plusIcon.classList.add('w-4');
            plusIcon.classList.add('h-4');
            minusIcon.classList.add('w-4');
            minusIcon.classList.add('h-4');
            $("div.accordian-lucide").html("")
            $("div.accordian-lucide").append(plusIcon)
            // Append HTMLElement in webpage
            const myApp = document.getElementById('app');
            if(tthis.hasClass("collapsed")) {
                
                //create minus sign
                tthis.children("div.accordian-lucide").html("");
                tthis.children("div.accordian-lucide").append(minusIcon)
                
                
            } else {
                //create plus sign
                tthis.children("div.accordian-lucide").html("");
                tthis.children("div.accordian-lucide").append(plusIcon)
            }   

            
        })
    /**
     * Accordian Button Finished
     */
    const activityModalCP = tailwind.Modal.getOrCreateInstance(document.querySelector("#addActivityModal"));

    $('.activity-call').on('click', function(e){
        e.preventDefault();
        let tthis = $(this)
        let planDateListId = tthis.data('plandataid');
        tthis.children('span').css('display', 'inline-block');
        tthis.attr('disabled', 'disabled');
        let data ={
            page: 1,
            size: 100,
            status:1,
        }

        axios({
            method: 'get',
            url: route('elearning.list', data),
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                tthis.removeAttr('disabled');
                tthis.children('span').css('display', 'none');
                
                const LearningData = response.data.data;
                
                let html = '';
                for (let i=0; i<LearningData.length; i++) {
                    let data =[planDateListId,LearningData[i].id
                    ]
                    if(LearningData[i].active==1) {
                      html += `<a href="${
                        route('tutor_module_activity.create',data)
                      }" data-tw-toggle="modal" data-tw-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                 <div class="box rounded-md p-3 relative zoom-in">
                                     <div class="flex-none relative block before:block before:w-full before:pt-[100%]">
                                         <div class="absolute top-0 left-0 w-full h-full image-fit">
                                             <img alt="Midone - HTML Admin Template" class="rounded-md" src="${
                                                LearningData[i].logo_url
                                             }">
                                         </div>
                                     </div>
                                     <div class="block font-medium text-center truncate mt-3">${
                                        LearningData[i].name
                                     }</div>
                                </div>
                             </a>`
                    }
                }

                $("#activit-contentlist").html(html)

                if(html!="") {
                    activityModalCP.show();
                }
            }
        }).catch(error =>{
            errorModal.show();
                document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                    $("#errorModal .title").html("Token Mismatch!" );
                    $("#errorModal .descrtiption").html('Please reload');
                }); 
            location.reload();
        });
        
    });

    
    /* Profile Menu End */
})();