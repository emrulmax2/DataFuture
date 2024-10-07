import IMask from 'imask';
import { createIcons, icons } from "lucide";

("use strict");    

document.addEventListener('DOMContentLoaded', function() {
    
    const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

    const comparer = (idx, asc) => (a, b) => ((v1, v2) => 
        v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
    )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

    document.querySelectorAll('#sortable-table th').forEach(th => th.addEventListener('click', function() {
        const table = th.closest('table');
        Array.from(table.querySelectorAll('tbody > tr'))
            .sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
            .forEach(tr => table.querySelector('tbody').appendChild(tr));

        // Reset all sorting icons to arrow-up-down
        document.querySelectorAll('#sortable-table th svg').forEach(svg => {
            svg.remove();
        });

        document.querySelectorAll('#sortable-table th').forEach(header => {
            const defaultIcon = document.createElement('i');
            defaultIcon.classList.add('w-4', 'h-4', 'ml-2','inline-flex');
            defaultIcon.setAttribute('data-lucide', 'arrow-up-down');
            header.appendChild(defaultIcon);
        });
        
        // Update sorting icons
        const icon = th.querySelector('svg');
        const defaultNewIcon = th.querySelector('i');
        defaultNewIcon.remove()
        if (icon) {
            icon.remove();
            
        }
        const newIcon = document.createElement('i');
        newIcon.classList.add('w-4', 'h-4', 'ml-2','inline-flex'); // Add classes to newIcon
        if (this.asc) {
            newIcon.setAttribute('data-lucide', 'arrow-up');
        } else {
            newIcon.setAttribute('data-lucide', 'arrow-down');
        }
        th.appendChild(newIcon);
        // Refresh Lucide icons with the icons object
        createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
        });
        paginateTable();
    }));

    function paginateTable() {
        const rowsPerPage = 10;
        const rows = $('#sortable-table tbody tr');
        const rowsCount = rows.length;
        const pageCount = Math.ceil(rowsCount / rowsPerPage);
        const numbers = $('#pagination-container');

        numbers.html('');

        for (let i = 0; i < pageCount; i++) {
            numbers.append('<a href="#">' + (i + 1) + '</a>');
        }

        rows.hide();
        rows.slice(0, rowsPerPage).show();

        numbers.find('a').click(function(e) {
            e.preventDefault();
            const index = $(this).index();
            const start = index * rowsPerPage;
            const end = start + rowsPerPage;

            rows.hide();
            rows.slice(start, end).show();
        });
    }

    paginateTable();
});

(function () {

    
    $(".timeMask").each(function () {
        var maskOptions = {
            mask: 'HH:MM',
            blocks: {
            HH: {
                    mask: IMask.MaskedRange,
                    placeholderChar: 'HH',
                    from: 0,
                    to: 23,
                    maxLength: 2
                },
            MM: {
                    mask: IMask.MaskedRange,
                    placeholderChar: 'MM',
                    from: 0,
                    to: 59,
                    maxLength: 2
                }
            }
        };
        var mask = IMask(this, maskOptions);
    });

    
    $(".tablepoint-toggle").on('click', function(e) {
        e.preventDefault();
        let tthis = $(this)
        let currentThis=tthis.children(".plusminus").eq(0);
        console.log(currentThis);
        let nextThis=tthis.children(".plusminus").eq(1);
        if(currentThis.hasClass('hidden') ) {
            currentThis.removeClass('hidden')
            nextThis.addClass('hidden')
        }else {
            nextThis.removeClass('hidden')
            currentThis.addClass('hidden')
        }

        tthis.parent().siblings('div.tabledataset').slideToggle();

    });
    $(".toggle-heading").on('click', function(e) {
        e.preventDefault();
        let tthis = $(this)
        tthis.siblings("div.tablepoint-toggle").trigger('click')
    })

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#delete-confirmation-modal"));
    const editAttemptModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAttemptModal"));
    const addAttemptModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAttemptModal"));
    // Confirm Modal Action

    $('.delete_btn').on('click', function(){
        let $statusBTN = $(this);

        let rowID = $statusBTN.attr('data-id');
        let confModalDelTitle = "Do you want to delete";
        confirmModal.show();
        document.getElementById('delete-confirmation-modal').addEventListener('shown.tw.modal', function(event){
            $('#delete-confirmation-modal .confModTitle').html(confModalDelTitle);
            $('#delete-confirmation-modal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
            $('#delete-confirmation-modal .agreeWith').attr('data-id', rowID);
            $('#delete-confirmation-modal .agreeWith').attr('data-action', 'DELETE');
        });
    });
    $('.edit_btn').on('click', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');
        let grade = $statusBTN.attr('data-grade');
        let publishTime = $statusBTN.attr('data-publishTime');
        let publishDate = $statusBTN.attr('data-publishDate');
        editAttemptModal.show();
        document.getElementById('editAttemptModal').addEventListener('shown.tw.modal', function(event){
            $('#editAttemptModal input[name="id"]').val(rowID);
            $('#editAttemptModal select[name="grade_id"]').val(grade);
            $('#editAttemptModal input[name="published_at"]').val(publishDate);
            $('#editAttemptModal input[name="published_time"]').val(publishTime);
        });
    });

    $('.add_btn').on('click', function(){
        let $statusBTN = $(this);
        let assessmentPlan = $statusBTN.attr('data-assessmentPlan');
        let plan = $statusBTN.attr('data-plan');
    
        document.getElementById('addAttemptModal').addEventListener('shown.tw.modal', function(event){
            $('#addAttemptModal input[name="assessment_plan_id"]').val(assessmentPlan);
            $('#addAttemptModal input[name="plan_id"]').val(plan);
        });
    });
    
    $("#addAttemptForm").on("submit", function (e) {

        e.preventDefault();
        const form = document.getElementById("addAttemptForm");

        document.querySelector('#save').setAttribute('disabled', 'disabled');
        document.querySelector('#save svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route("result.store.single"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#save").removeAttribute("disabled");
                document.querySelector("#save svg").style.cssText = "display: none;";
                addAttemptModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Success!");
                    $("#successModal .successModalDesc").html('Result updated');
                });
            }
            location.reload();
        }).catch((error) => {
            document.querySelector("#save").removeAttribute("disabled");
            document.querySelector("#save svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editForm .${key}`).addClass('border-danger')
                        $(`#editForm  .error-${key}`).html(val)
                    }
                }else {
                    console.log("error");
                }
            }
        });
    });
    $("#editAttemptForm").on("submit", function (e) {
        let editId = $('#editAttemptForm input[name="id"]').val();

        e.preventDefault();
        const form = document.getElementById("editAttemptForm");

        document.querySelector('#update').setAttribute('disabled', 'disabled');
        document.querySelector('#update svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route("result.update", editId),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#update").removeAttribute("disabled");
                document.querySelector("#update svg").style.cssText = "display: none;";
                editAttemptModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Success!");
                    $("#successModal .successModalDesc").html('Result updated');
                });
            }
            location.reload();
        }).catch((error) => {
            document.querySelector("#update").removeAttribute("disabled");
            document.querySelector("#update svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editForm .${key}`).addClass('border-danger')
                        $(`#editForm  .error-${key}`).html(val)
                    }
                }else {
                    console.log("error");
                }
            }
        });
    });

    $('#delete-confirmation-modal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let resultId = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#delete-confirmation-modal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('result.destroy', resultId),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {

                    $('#delete-confirmation-modal button').removeAttr('disabled');
                    confirmModal.hide();
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Data successfully deleted.');
                    });

                    location.reload();

                }
            }).catch(error =>{
                console.log(error)
            });
        } 
    })
    // $('#sortable-table th').on('click', function() {
    //     var table = $(this).parents('table').eq(0);
    //     var rows = table.find('tbody tr').toArray().sort(comparer($(this).index()));
    //     this.asc = !this.asc;
    //     if (!this.asc) {
    //         rows = rows.reverse();
    //     }
    //     for (var i = 0; i < rows.length; i++) {
    //         table.append(rows[i]);
    //     }
    // });

    // function comparer(index) {
    //     return function(a, b) {
    //         var valA = getCellValue(a, index);
    //         var valB = getCellValue(b, index);
    //         return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);
    //     };
    // }

    // function getCellValue(row, index) {
    //     return $(row).children('td').eq(index).text();
    // }

})()