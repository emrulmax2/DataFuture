(function () {
        const addWpSettingModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addWpSettingModal"));
        const editWpSettingModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editWpSettingModal"));
        const addWpSettingTypeModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addWpSettingTypeModal"));
        const editWpSettingTypeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editWpSettingTypeModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        document.querySelector("#addWpSettingModal").addEventListener('hide.tw.modal', function(event) {
            $('#addWpSettingModal .acc__input-error').html('');
            $('#addWpSettingModal input').val('');
        });
        
        document.getElementById('editWpSettingModal').addEventListener('hide.tw.modal', function(event) {
            $('#editWpSettingModal .acc__input-error').html('');
            $('#editWpSettingModal input').val('');
            $('#editWpSettingModal input[name="id"]').val('0');
        }); 
        document.querySelector("#addWpSettingTypeModal").addEventListener('hide.tw.modal', function(event) {
            $('#addWpSettingTypeModal .acc__input-error').html('');
            $('#addWpSettingTypeModal input').val('');
        });
        
        document.getElementById('editWpSettingTypeModal').addEventListener('hide.tw.modal', function(event) {
            $('#editWpSettingTypeModal .acc__input-error').html('');
            $('#editWpSettingTypeModal input').val('');
            $('#editWpSettingTypeModal input[name="id"]').val('0');
        }); 

        document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
            $('#confirmModal .agreeWith').attr('data-id', '0');
            $('#confirmModal .agreeWith').attr('data-action', 'none');
        });

        $('#addWpSettingForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addWpSettingForm');
        
            document.querySelector('#wpSettingInsertBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#wpSettingInsertBtn svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('workplacement-settings.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#wpSettingInsertBtn').removeAttribute('disabled');
                document.querySelector("#wpSettingInsertBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#wpSettingInsertBtn').removeAttribute('disabled');
                    document.querySelector("#wpSettingInsertBtn svg").style.cssText = "display: none;";
                    $('#addWpSettingForm #name').val('');
                    addWpSettingModal.hide();
                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(
                                "Success!"
                            );
                            $("#successModal .successModalDesc").html(response.data.message);
                        });                  
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1500)
            }).catch(error => {
                document.querySelector('#wpSettingInsertBtn').removeAttribute('disabled');
                document.querySelector("#wpSettingInsertBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addWpSettingForm .${key}`).addClass('border-danger')
                            $(`#addWpSettingForm  .error-${key}`).html(val)
                        }
                        $('#addWpSettingForm #name').val('');
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $(document).on("click", ".editWpSetting_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("workplacement-settings.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editWpSettingForm input[name="name"]').val(dataset.name ? dataset.name : '');
                    $('#editWpSettingForm input[name="setting_id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $("#editWpSettingForm").on("submit", function (e) {
            let editId = $('#editWpSettingForm input[name="setting_id"]').val();

            e.preventDefault();
            const form = document.getElementById("editWpSettingForm");

            document.querySelector('#updateWpSettingBtn').setAttribute('disabled', 'disabled');
            document.querySelector('#updateWpSettingBtn svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("workplacement-settings.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateWpSettingBtn").removeAttribute("disabled");
                    document.querySelector("#updateWpSettingBtn svg").style.cssText = "display: none;";
                    editWpSettingModal.hide();

                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(
                                "Success!"
                            );
                            $("#successModal .successModalDesc").html(response.data.message);
                        });
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1500)
            }).catch((error) => {
                document.querySelector("#updateWpSettingBtn").removeAttribute("disabled");
                document.querySelector("#updateWpSettingBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editWpSettingForm .${key}`).addClass('border-danger')
                            $(`#editWpSettingForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editWpSettingModal.hide();

                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html("No Data Change!");
                            $("#successModal .successModalDesc").html(message);
                        });
                    } else {
                        console.log("error");
                    }
                }
            });
        });


        $(document).on("click", ".wpSettingDelete_btn", function () {   
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');
            let route = $statusBTN.attr('data-route');
        
        
            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-route', route);
        
            });
        });


        $(".addWpSettingType_btn").on("click", function () {      
            let $wp_id = $(this);
            let wp_id = $wp_id.attr("data-id");
            $('#addWpSettingTypeForm input[name="workplacement_setting_id"]').val(wp_id);
        });


        $('#addWpSettingTypeForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addWpSettingTypeForm');
        
            document.querySelector('#wpSettingTypeInsertBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#wpSettingTypeInsertBtn svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('workplacement-setting.types.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#wpSettingTypeInsertBtn').removeAttribute('disabled');
                document.querySelector("#wpSettingTypeInsertBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#wpSettingTypeInsertBtn').removeAttribute('disabled');
                    document.querySelector("#wpSettingTypeInsertBtn svg").style.cssText = "display: none;";

                    addWpSettingTypeModal.hide();
                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(
                                "Success!"
                            );
                            $("#successModal .successModalDesc").html(response.data.message);
                        });                  
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1500)
            }).catch(error => {
                document.querySelector('#wpSettingTypeInsertBtn').removeAttribute('disabled');
                document.querySelector("#wpSettingTypeInsertBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addWpSettingTypeForm .${key}`).addClass('border-danger')
                            $(`#addWpSettingTypeForm  .error-${key}`).html(val)
                        }
                        $('#addWpSettingTypeForm #name').val('');
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $(document).on("click", ".editWpSettingType_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("workplacement-setting.types.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editWpSettingTypeForm input[name="type"]').val(dataset.type ? dataset.type : '');
                    $('#editWpSettingTypeForm input[name="setting_type_id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $("#editWpSettingTypeForm").on("submit", function (e) {
            let editId = $('#editWpSettingTypeForm input[name="setting_type_id"]').val();

            e.preventDefault();
            const form = document.getElementById("editWpSettingTypeForm");

            document.querySelector('#updateWpSettingTypeBtn').setAttribute('disabled', 'disabled');
            document.querySelector('#updateWpSettingTypeBtn svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("workplacement-setting.types.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateWpSettingTypeBtn").removeAttribute("disabled");
                    document.querySelector("#updateWpSettingTypeBtn svg").style.cssText = "display: none;";
                    editWpSettingTypeModal.hide();

                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(
                                "Success!"
                            );
                            $("#successModal .successModalDesc").html(response.data.message);
                        });
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1500)
            }).catch((error) => {
                document.querySelector("#updateWpSettingTypeBtn").removeAttribute("disabled");
                document.querySelector("#updateWpSettingTypeBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editWpSettingTypeForm .${key}`).addClass('border-danger')
                            $(`#editWpSettingTypeForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editWpSettingTypeModal.hide();

                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html("No Data Change!");
                            $("#successModal .successModalDesc").html(message);
                        });
                    } else {
                        console.log("error");
                    }
                }
            });
        });


        $(document).on("click", ".wpSettingTypeDelete_btn", function () {   
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');
            let route = $statusBTN.attr('data-route');
        
        
            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-route', route);
        
            });
        });

        $('#confirmModal .agreeWith').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');
            let route = $agreeBTN.attr('data-route');
        
            $('#confirmModal button').attr('disabled', 'disabled');
                axios({
                    method: 'delete',
                    url: route,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();
        
                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html(response.data.message);
                        });
                    }
        
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
        
                }).catch(error =>{
                    console.log(error)
                });
        })
        
})();