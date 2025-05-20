const addLevelHoursModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addLevelHoursModal"));
const editLevelHoursModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#levelHoursEditModal"));
const addLearningHoursModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addLearningHoursModal"));
const editLearningHoursModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editLearningHoursModal"));
const workplacementAddModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#workplacementAddModal"));
const workplacementEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#workplacementEditModal"));
const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
let confModalDelTitle = 'Are you sure?';



$('#addWorkPlacementForm').on('submit', function(e){
    e.preventDefault();
    const form = document.getElementById('addWorkPlacementForm');

    document.querySelector('#insertWorkPlacement').setAttribute('disabled', 'disabled');
    document.querySelector("#insertWorkPlacement svg").style.cssText = "display: inline-block;";

    let form_data = new FormData(form);

    axios({
        method: "post",
        url: route('workplacement.store'),
        data: form_data,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {
        document.querySelector('#insertWorkPlacement').removeAttribute('disabled');
        document.querySelector("#insertWorkPlacement svg").style.cssText = "display: none;";
        
        if (response.status == 200) {
            workplacementAddModal.hide();

            succModal.show();
            document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                $("#successModal .successModalTitle").html("Congratulations!");
                $("#successModal .successModalDesc").html(response.data.message);
            });     
            
            form.reset();
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    }).catch(error => {
        document.querySelector('#insertWorkPlacement').removeAttribute('disabled');
        document.querySelector("#insertWorkPlacement svg").style.cssText = "display: none;";
        
        if (error.response) {
            if (error.response.status == 422) {
                $('.acc__input-error').html('');
                $('.form-control').removeClass('border-danger');
                
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#addWorkPlacementForm .${key}`).addClass('border-danger');
                    $(`#addWorkPlacementForm .error-${key}`).html(val);
                }
            } else {
                console.log('error', error);
            }
        }
    });
});


$('#addLevelHoursForm').on('submit', function(e){
    e.preventDefault();
    const form = document.getElementById('addLevelHoursForm');

    document.querySelector('#insertLevelHours').setAttribute('disabled', 'disabled');
    document.querySelector("#insertLevelHours svg").style.cssText = "display: inline-block;";

    let form_data = new FormData(form);

    axios({
        method: "post",
        url: route('level.hours.store'),
        data: form_data,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {
        document.querySelector('#insertLevelHours').removeAttribute('disabled');
        document.querySelector("#insertLevelHours svg").style.cssText = "display: none;";
        
        if (response.status == 201) {
            addLevelHoursModal.hide();

            succModal.show();
            document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                $("#successModal .successModalTitle").html("Congratulations!");
                $("#successModal .successModalDesc").html(response.data.message);
            });     
            
            form.reset();
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    }).catch(error => {
        document.querySelector('#insertLevelHours').removeAttribute('disabled');
        document.querySelector("#insertLevelHours svg").style.cssText = "display: none;";
        
        if (error.response) {
            if (error.response.status == 422) {
                $('.acc__input-error').html('');
                $('.form-control').removeClass('border-danger');
                
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#addLevelHoursForm .${key}`).addClass('border-danger');
                    $(`#addLevelHoursForm .error-${key}`).html(val);
                }
            } else {
                console.log('error', error);
            }
        }
    });
});


$('#addLearningHoursForm').on('submit', function(e){
    e.preventDefault();
    const form = document.getElementById('addLearningHoursForm');

    document.querySelector('#insertLearningHours').setAttribute('disabled', 'disabled');
    document.querySelector("#insertLearningHours svg").style.cssText = "display: inline-block;";

    let form_data = new FormData(form);

    axios({
        method: "post",
        url: route('learning.hours.store'),
        data: form_data,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {
        document.querySelector('#insertLearningHours').removeAttribute('disabled');
        document.querySelector("#insertLearningHours svg").style.cssText = "display: none;";
        
        if (response.status == 201) {
            addLearningHoursModal.hide();

            succModal.show();
            document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                $("#successModal .successModalTitle").html("Congratulations!");
                $("#successModal .successModalDesc").html(response.data.message);
            });     
            
            form.reset();
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    }).catch(error => {
        document.querySelector('#insertLearningHours').removeAttribute('disabled');
        document.querySelector("#insertLearningHours svg").style.cssText = "display: none;";
        
        if (error.response) {
            if (error.response.status == 422) {
                $('.acc__input-error').html('');
                $('.form-control').removeClass('border-danger');
                
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#addLearningHoursForm .${key}`).addClass('border-danger');
                    $(`#addLearningHoursForm .error-${key}`).html(val);
                }
            } else {
                console.log('error', error);
            }
        }
    });
});


$(".addLevelHours_btn").on("click", function () {      
    let $wp_id = $(this);
    let wp_id = $wp_id.attr("data-id");
    $('#addLevelHoursForm input[name="workplacement_id"]').val(wp_id);
});
$(".addLearningHours_btn").on("click", function () {      
    let $wp_id = $(this);
    let wp_id = $wp_id.attr("data-id");
    $('#addLearningHoursForm input[name="level_hours_id"]').val(wp_id);
});


$(".editWorkPlacement_btn").on("click", function () {      
    let $editBtn = $(this);
    let editId = $editBtn.attr("data-id");

    axios({
        method: "get",
        url: route("workplacement.edit", editId),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    })
        .then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                $('#workplacementEditModal input[name="name"]').val(dataset.name ? dataset.name : '');
                $('#workplacementEditModal input[name="hours"]').val(dataset.hours ? dataset.hours : '');
                $('#workplacementEditModal select[name="course_id"]').val(dataset.course_id ? dataset.course_id : '');
                $('#workplacementEditModal input[name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                $('#workplacementEditModal input[name="end_date"]').val(dataset.end_date ? dataset.end_date : '');
                $('#workplacementEditModal #editWorkPlacementForm').attr('data-form-id', editId);

            }
        })
        .catch((error) => {
            console.log(error);
        });
});


$(".editLevelHours_btn").on("click", function () {      
    let $editBtn = $(this);
    let editId = $editBtn.attr("data-id");

    axios({
        method: "get",
        url: route("level.hours.edit", editId),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    })
    .then((response) => {
        if (response.status == 200) {
            let dataset = response.data;
            $('#editLevelHoursForm input[name="name"]').val(dataset.name ? dataset.name : '');
            $('#editLevelHoursForm input[name="hours"]').val(dataset.hours ? dataset.hours : '');
            $('#levelHoursEditModal #editLevelHoursForm').attr('data-form-id', editId);
            $('#editLevelHoursForm input[name="workplacement_id"]').val(dataset.workplacement_details_id);
        }
    })
    .catch((error) => {
        console.log(error);
    });
});
$(".editLearningHours_btn").on("click", function () {      
    let $editBtn = $(this);
    let editId = $editBtn.attr("data-id");

    axios({
        method: "get",
        url: route("learning.hours.edit", editId),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    })
    .then((response) => {
        if (response.status == 200) {
            let dataset = response.data;
            $('#editLearningHoursForm input[name="name"]').val(dataset.name ? dataset.name : '');
            $('#editLearningHoursForm input[name="hours"]').val(dataset.hours ? dataset.hours : '');
            $('#editLearningHoursModal #editLearningHoursForm').attr('data-form-id', editId);
            $('#editLearningHoursForm input[name="level_hours_id"]').val(dataset.level_hours_id);
        }
    })
    .catch((error) => {
        console.log(error);
    });
});



$('#editLevelHoursForm').on('submit', function(e){
    e.preventDefault();

    const form = document.getElementById('editLevelHoursForm');

    document.querySelector('#updateLevelHours').setAttribute('disabled', 'disabled');
    document.querySelector("#updateLevelHours svg").style.cssText = "display: inline-block;";

    let form_data = new FormData(form);

    let formId = $('#editLevelHoursForm').attr('data-form-id');

    axios({
        method: "POST",
        url: route("level.hours.update", formId),
        data: form_data,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {
        document.querySelector('#updateLevelHours').removeAttribute('disabled');
        document.querySelector("#updateLevelHours svg").style.cssText = "display: none;";
        
        if (response.status == 200) {
            editLevelHoursModal.hide();

            succModal.show();
            document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                $("#successModal .successModalTitle").html("Congratulations!");
                $("#successModal .successModalDesc").html(response.data.message);
            });     

            setTimeout(() => {
                window.location.reload();
            }, 1500);
            
            
        }
    }).catch(error => {
        document.querySelector('#updateLevelHours').removeAttribute('disabled');
        document.querySelector("#updateLevelHours svg").style.cssText = "display: none;";
        
        if (error.response) {
            if (error.response.status == 422) {
                $('.acc__input-error').html('');
                $('.form-control').removeClass('border-danger');
                
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#editLevelHoursForm .${key}`).addClass('border-danger');
                    $(`#editLevelHoursForm .error-${key}`).html(val);
                }
            } else {
                console.log('error', error);
            }
        }
    });
});
$('#editLearningHoursForm').on('submit', function(e){
    e.preventDefault();

    const form = document.getElementById('editLearningHoursForm');

    document.querySelector('#updateLearningHours').setAttribute('disabled', 'disabled');
    document.querySelector("#updateLearningHours svg").style.cssText = "display: inline-block;";

    let form_data = new FormData(form);

    let formId = $('#editLearningHoursForm').attr('data-form-id');

    axios({
        method: "POST",
        url: route("learning.hours.update", formId),
        data: form_data,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {
        document.querySelector('#updateLearningHours').removeAttribute('disabled');
        document.querySelector("#updateLearningHours svg").style.cssText = "display: none;";
        
        if (response.status == 200) {
            editLearningHoursModal.hide();

            succModal.show();
            document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                $("#successModal .successModalTitle").html("Congratulations!");
                $("#successModal .successModalDesc").html(response.data.message);
            });     

            setTimeout(() => {
                window.location.reload();
            }, 1500);
            
            
        }
    }).catch(error => {
        document.querySelector('#updateLearningHours').removeAttribute('disabled');
        document.querySelector("#updateLearningHours svg").style.cssText = "display: none;";
        
        if (error.response) {
            if (error.response.status == 422) {
                $('.acc__input-error').html('');
                $('.form-control').removeClass('border-danger');
                
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#editLearningHoursForm .${key}`).addClass('border-danger');
                    $(`#editLearningHoursForm .error-${key}`).html(val);
                }
            } else {
                console.log('error', error);
            }
        }
    });
});

$('#editWorkPlacementForm').on('submit', function(e){
    e.preventDefault();

    const form = document.getElementById('editWorkPlacementForm');

    document.querySelector('#updateWorkPlacement').setAttribute('disabled', 'disabled');
    document.querySelector("#updateWorkPlacement svg").style.cssText = "display: inline-block;";

    let form_data = new FormData(form);

    let formId = $('#editWorkPlacementForm').attr('data-form-id');
    console.log(form_data.entries())

    axios({
        method: "POST",
        url: route("workplacement.update", formId),
        data: form_data,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {
        document.querySelector('#updateWorkPlacement').removeAttribute('disabled');
        document.querySelector("#updateWorkPlacement svg").style.cssText = "display: none;";
        
        if (response.status == 200) {
            workplacementEditModal.hide();

            succModal.show();
            document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                $("#successModal .successModalTitle").html("Congratulations!");
                $("#successModal .successModalDesc").html(response.data.message);
            });     

            setTimeout(() => {
                window.location.reload();
            }, 1500);
            
            
        }
    }).catch(error => {
        document.querySelector('#updateWorkPlacement').removeAttribute('disabled');
        document.querySelector("#updateWorkPlacement svg").style.cssText = "display: none;";
        
        if (error.response) {
            if (error.response.status == 422) {
                $('.acc__input-error').html('');
                $('.form-control').removeClass('border-danger');
                
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#editWorkPlacementForm .${key}`).addClass('border-danger');
                    $(`#editWorkPlacementForm .error-${key}`).html(val);
                }
            } else {
                console.log('error', error);
            }
        }
    });
});


$(".delete_btn").on("click", function () {   
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
