(function(){

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const editContactModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editContactModal"));
    const setButtonLoading = function(selector, loading) {
        const button = document.querySelector(selector);
        if(!button) return;

        const loader = button.querySelector('svg:not([data-lucide])');
        if(loading) {
            button.setAttribute('disabled', 'disabled');
        } else {
            button.removeAttribute('disabled');
        }

        if(loader) {
            loader.style.cssText = loading ? 'display: inline-block;' : 'display: none;';
        }
    };
    
if($('#addressModal').length > 0){
    const addressModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addressModal"));

    const addressModalEl = document.getElementById('addressModal')
    addressModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addressModal .acc__input-error').html('');
        $('#addressModal .modal-body input').val('');
        $('#addressModal input[name="address_id"]').val('0');
    });

    $('.addressPopupToggler').on('click', function(e){
        e.preventDefault();

        var $btn = $(this);
        var $wrap = $btn.parents('.addressWrap');
        var $addressIdField = $btn.siblings('.address_id_field');

        var wrap_id = '#'+$wrap.attr('id');
        var address_id = $addressIdField.val();
        if(address_id > 0){
            axios({
                method: "post",
                url: route('address.get'),
                data: {address_id : address_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    var dataset = response.data.res;
                    
                    $('#addressModal #student_address_address_line_1').val(dataset.address_line_1 ? dataset.address_line_1 : '');
                    $('#addressModal #student_address_address_line_2').val(dataset.address_line_2 ? dataset.address_line_2 : '');
                    $('#addressModal #student_address_city').val(dataset.city ? dataset.city : '');
                    $('#addressModal #student_address_state_province_region').val(dataset.state ? dataset.state : '');
                    $('#addressModal #student_address_postal_zip_code').val(dataset.post_code ? dataset.post_code : '');
                    $('#addressModal #student_address_country').val(dataset.country ? dataset.country : '');

                    $('#addressModal input[name="place"]').val(wrap_id);
                    $('#addressModal input[name="address_id"]').val(address_id);
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $('#addressModal input[name="place"]').val(wrap_id);
            $('#addressModal .modal-body input').val('');
            $('#addressModal input[name="address_id"]').val('0');
        }
    });

    $('#addressForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addressForm');
        var $form = $(this);
        //var wrapid = $('input[name="place"]', $form).val();
        var address_id = $('input[name="address_id"]', $form).val();

        
        //var htmls = '';
        //var post_code = $('#student_address_postal_zip_code', $form).val();
        // htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_1', $form).val()+'</span><br/>';
        // if($('#student_address_address_line_2', $form).val() != ''){
        //     htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_2', $form).val()+'</span><br/>';
        // }
        // htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_city', $form).val()+'</span>, ';
        // if($('#student_address_state_province_region', $form).val() != ''){
        //     htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_state_province_region', $form).val()+'</span>, <br/>';
        // }else{
        //     htmls += '<br/>';
        // }
        // htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_postal_zip_code', $form).val()+'</span>,<br/>';
        // htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_country', $form).val()+'</span><br/>';

        setButtonLoading('#insertAddress', true);

        let agentId = $('#addressForm input[name="id"]').val();
        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('agent-user.address.store',agentId),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            setButtonLoading('#insertAddress', false);
            
            if (response.status == 200) {
                //var dataset = response.data.res;
                //var newAddressId = (dataset.id ? dataset.id : 0);
                addressModal.hide();
                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Data successfully updated.');
                });
                location.reload();
            }
            
        }).catch(error => {

            setButtonLoading('#insertAddress', false);
            if(error.response){
                if(error.response.status == 422){
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addressForm .${key}`).addClass('border-danger')
                        $(`#addressForm  .error-${key}`).html(val)
                    }
                }else{
                    console.log('error');
                }
            }
        });
    });
};

 // Update Contact Data
 $("#editContactModal").on("submit", function (e) {

    e.preventDefault();
    let editId = $('#editContactModal input[name="id"]').val();

    const form = document.getElementById("editContactModalForm");

    setButtonLoading('#update', true);

    let form_data = new FormData(form);

    axios({
        method: "post",
        url: route("agent-user.update", editId),
        data: form_data,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    }).then((response) => {
        if (response.status == 200) {
            setButtonLoading('#update', false);
            editContactModal.hide();
            $("#successModal .successModalTitle").html("Congratulations!");
            $("#successModal .successModalDesc").html('Data successfully updated.');
            succModal.show();

            location.reload();
        }
    }).catch((error) => {
        setButtonLoading('#update', false);
        if (error.response) {
            if (error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#editContactModalForm .${key}`).addClass('border-danger')
                    $(`#editContactModalForm  .error-${key}`).html(val)
                }
            }else if (error.response.status == 304) {
                editContactModal.hide();

                let message = error.response.statusText;
                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Oops!");
                    $("#successModal .successModalDesc").html(message);
                });
            } else {
                console.log("error");
            }
        }
    });
});
})();
