import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";

(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    
    //var employment_status = new TomSelect('#employment_status', tomOptions);

    $('.lccTom').each(function(){
        if ($(this).attr("multiple") !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: "Remove this item",
                    },
                }
            };
        }
        new TomSelect(this, tomOptions);
    });

    const editStudentCourseDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudentCourseDetailsModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#editStudentCourseDetailsForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editStudentCourseDetailsForm');
    
        document.querySelector('#savePCP').setAttribute('disabled', 'disabled');
        document.querySelector("#savePCP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.course.details'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#savePCP').removeAttribute('disabled');
                document.querySelector("#savePCP svg").style.cssText = "display: none;";

                editStudentCourseDetailsModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Course & Programme Details successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.show();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#savePCP').removeAttribute('disabled');
            document.querySelector("#savePCP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editStudentCourseDetailsForm .${key}`).addClass('border-danger');
                        $(`#editStudentCourseDetailsForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})();