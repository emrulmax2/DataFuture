import IMask from 'imask';
import xlsx from "xlsx";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import { createIcons, icons } from "lucide";
import { createElement, Plus,Minus } from 'lucide';
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Dropzone from "dropzone";

(function(){

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    let addEditor = ""
    if($("#addEditor").length > 0){
        const el = document.getElementById('addEditor');
        ClassicEditor.create(el).then(newEditor => {
            addEditor = newEditor;
        }).catch((error) => {
            console.error(error);
        });
    }
    /* Start Dropzone */
    if($("#uploadDocumentForm").length > 0){
        let dzError = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadDocumentForm = {
            autoProcessQueue: false,
            maxFiles: 10,
            maxFilesize: 20,
            parallelUploads: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
            addRemoveLinks: true,
            thumbnailWidth: 100,
            thumbnailHeight: 100,
        };

        let options = {
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
        };


        var drzn1 = new Dropzone('#uploadDocumentForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#uploadDocumentModal .modal-content .uploadError').remove();
            $('#uploadDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#uploadDocumentModal .modal-content .uploadError').remove();
            }, 4000)
        });

        drzn1.on("error", function(file, response){
            dzError = true;
        });

        drzn1.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on("complete", function(file) {
            //drzn1.removeFile(file);
        }); 

        drzn1.on('queuecomplete', function(){
            $('#activitySave').removeAttr('disabled');
            document.querySelector("#activitySave span").style.cssText ="display: none;";

            if(!dzError){
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Success!" );
                    $("#successModal .successModalDesc").html('Assignment Created Successfully');
                    //$("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    //window.location.reload();
                    let planId = $("input[name='plan_id']").val();
                    location.href = route('tutor-dashboard.plan.module.show',planId);
                }, 5000);
            }else{
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!" );
                    $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact administrator.');
                    $("#warningModal .warningCloser").attr('data-action', 'DISMISS');
                });
                setTimeout(function(){
                    warningModal.hide();
                    //window.location.reload();
                }, 1000);
            }
        })

        $('#activitySave').on('click', function(e){
            e.preventDefault();
            let tthis = $(this);
            //document.querySelector('#activitySave').setAttribute('disabled', 'disabled');
            document.querySelector("#activitySave span").style.cssText ="display: inline-block;";
            
            const title = $("input[name='title']").val();
            $("input[name='name']").val(title);
            const description = addEditor.getData();
            $("input[name='description']").val(description);
            const availability = $("input[name='start_date']").val();
            const plans_date_list_id =  $("input[name='plans_date_list_id']").val();
            const activity_settings_id = $("input[name='activity_settings_id']").val();
            $("input[name='availibility_at']").val(availability);
    
            if(title!="") {
                $('.error-title').html('');
                $("input[name='title']").removeClass('border-danger')
            } else {
                $('.error-title').html('Title Field can not be empty');
                $("input[name='title']").addClass('border-danger')
            }
            
            let data = {
                name : title,
                description : description,
                availibility_at : availability,
                activity_settings_id : activity_settings_id,
                plans_date_list_id : plans_date_list_id,
                
            }
            axios({
                method: 'post',
                url: route('tutor_module_activity.store', data),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    //tthis.removeAttr('disabled');
                    //tthis.children('span').css('display', 'none');
                    const LearningData = response.data.data;
                    
                    $("input[name='plan_content_id']").val(LearningData.plan_content_id);
                    if(LearningData)
                        drzn1.processQueue();
                    //let html = '';
                    // for (let i=0; i<LearningData.length; i++) {
                    //     let data =[planDateListId,LearningData[i].id
                    //     ]
                    //     if(LearningData[i].active==1) {
                    //       html += `<a href="${
                    //         route('tutor_module_activity.create',data)
                    //       }" data-tw-toggle="modal" data-tw-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                    //                  <div class="box rounded-md p-3 relative zoom-in">
                    //                      <div class="flex-none relative block before:block before:w-full before:pt-[100%]">
                    //                          <div class="absolute top-0 left-0 w-full h-full image-fit">
                    //                              <img alt="Midone - HTML Admin Template" class="rounded-md" src="${
                    //                                 LearningData[i].logo_url
                    //                              }">
                    //                          </div>
                    //                      </div>
                    //                      <div class="block font-medium text-center truncate mt-3">${
                    //                         LearningData[i].name
                    //                      }</div>
                    //                 </div>
                    //              </a>`
                    //     }
                    // }
    
                    //$("#activit-contentlist").html(html)
    
                    // if(html!="") {
                    //     activityModalCP.show();
                    // }
                }
            }).catch(error =>{
                errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .title").html("Token Mismatch!" );
                        $("#errorModal .descrtiption").html('Please reload');
                    }); 
                location.reload();
            });
            
            // if($('#uploadDocumentModal [name="hard_copy_check_status"]:checked').length > 0){
            //     var hardCopyChecked = $('#uploadDocumentModal [name="hard_copy_check_status"]:checked').val();
            //     $('#uploadDocumentModal input[name="hard_copy_check"]').val(hardCopyChecked)
                
            // }else{
            //     $('#uploadDocumentModal .modal-content .uploadError').remove();
            //     $('#uploadDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select the hard copy check status.</div>');
                
            //     createIcons({
            //         icons,
            //         "stroke-width": 1.5,
            //         nameAttr: "data-lucide",
            //     });

            //     setTimeout(function(){
            //         $('#uploadDocumentModal .modal-content .uploadError').remove();
            //         document.querySelector('#uploadDocBtn').removeAttribute('disabled', 'disabled');
            //         document.querySelector("#uploadDocBtn svg").style.cssText ="display: none;";
            //     }, 5000)
            // }
            
        });
    }
    /* End Dropzone */

    

    

})();