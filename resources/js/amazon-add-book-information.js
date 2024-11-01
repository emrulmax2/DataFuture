import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

import dayjs from "dayjs";
import Litepicker from "litepicker";

(function(){
    let libDateOpt = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        inlineMode: false,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $('#the_location_name').on('keyup paste', function(e){
        $('#locationFieldRow .venuCol').remove();
        $('#isbnFieldRow').fadeOut('fast', function(){
            $('#isbn_no', this).val('');
            $('#isbnBooksWrap', this).fadeOut();
            $('#amazonAddBooksTable').html('');
        });
        $('#bookBarCodeWrap').fadeOut('fast', function(){
            $('input', this).val('');
            $('#saveBookBtn').fadeOut();
        });
    });
    $('#scanLocationBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var theLocation = $('#the_location_name').val();

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg.theLoader').fadeIn();
        $('.error-the_location_name').html('');

        if(theLocation != ''){
            $.ajax({
                method: 'POST',
                url: route('library.books.validate.location'),
                data: {location_name : theLocation},
                dataType: 'json',
                async: false,
                enctype: 'multipart/form-data',
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (res, textStatus, xhr) {
                    $theBtn.removeAttr('disabled');
                    $theBtn.find('svg.theLoader').fadeOut();
                    $('.error-the_location_name').html('');

                    $('#locationFieldRow .venuCol').remove();
                    $('#locationFieldRow').append(res.row);

                    $('#isbnFieldRow').fadeIn('fast', function(){
                        $('#isbn_no', this).val('');
                        $('#isbnBooksWrap', this).fadeOut();
                        $('#amazonAddBooksTable').html('');
                    });
                    $('#bookBarCodeWrap').fadeOut('fast', function(){
                        $('input', this).val('');
                        $('#saveBookBtn').fadeOut();
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if(jqXHR.status == 422){
                        $('.error-the_location_name').html('Location does not exist.')
                    }
                },
            });
        }else{
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg.theLoader').fadeOut();
            $('.error-the_location_name').html('This field is required.')
        }
    });

    $('#scanISBNBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var theIsbnNo = $('#isbn_no').val();

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg.theLoader').fadeIn('fast');
        $('#isbnBooksWrap').fadeOut('fast', function(){
            $('#amazonAddBooksTable').html('');
        });
        $('#bookBarCodeWrap').fadeOut('fast', function(){
            $('input', this).val('');
            $('#saveBookBtn').fadeOut();
        });

        if(theIsbnNo != ''){
            $.ajax({
                method: 'POST',
                url: route('library.books.validate.isbn'),
                data: {isbn : theIsbnNo},
                dataType: 'json',
                async: false,
                enctype: 'multipart/form-data',
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (res, textStatus, xhr) {
                    $theBtn.removeAttr('disabled');
                    $theBtn.find('svg.theLoader').fadeOut();
                    $('.error-isbn_no').html('');
                    if(xhr.status == 200 && res.suc == 1){
                        $('#isbnBooksWrap').fadeIn('fast', function(){
                            $('#amazonAddBooksTable').html(res.html);
                        })
                    }else if(xhr.status == 200 && res.suc == 2){
                        $('#isbnBooksWrap').fadeIn('fast', function(){
                            $('#amazonAddBooksTable').html(res.html);
                        })
                    }

                    setTimeout(() => {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });

                        if($('#isbnBooksWrap .datepicker').length > 0){
                            $('#isbnBooksWrap .datepicker').each(function(){
                                new Litepicker({
                                    element: this,
                                    ...libDateOpt
                                });
                            })
                        }
                    }, 500);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $theBtn.removeAttr('disabled');
                    $theBtn.find('svg.theLoader').fadeOut();
                    if(jqXHR.status == 422){
                        $('.error-isbn_no').html('ISBN does not exist.');
                        $('#isbnBooksWrap').fadeOut('fast', function(){
                            $('#amazonAddBooksTable').html(res.html);
                        });
                        $('#bookBarCodeWrap').fadeOut('fast', function(){
                            $('input', this).val('');
                            $('#saveBookBtn').fadeOut();
                        });
                    }
                },
            });
        }else{
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg.theLoader').fadeOut();
            $('.error-isbn_no').html('This field is required.');
        }
    });

    $('#amazonAddBooksTable').on('change', '.bookCheck', function(){
        var bookLength = $('#amazonAddBooksTable .bookCheck:checked').length;
        if(bookLength > 0){
            $('#bookBarCodeWrap').fadeIn('fast', function(){
                $('input', this).val('');
                $('#saveBookBtn').fadeOut();
            });
        }else{
            $('#bookBarCodeWrap').fadeOut('fast', function(){
                $('input', this).val('');
                $('#saveBookBtn').fadeOut();
            });
        }
    });

    $('#amazonAddBooksTable').on('click', '#scanCustomBook', function(e){
        e.preventDefault();
        var $theBtn = $(this);

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg.theLoader').fadeIn('fast');

        var errorCount = 0;
        $('#amazonAddBooksTable').find('.require').each(function(){
            if($(this).val() == ''){
                errorCount += 1;
                $(this).siblings('.acc__input-error').html('This field is required.');
            }else{
                $(this).siblings('.acc__input-error').html('');
            }
        });
        if ($('#book_image').get(0).files.length === 0) {
            errorCount += 1;
            $('#book_image').siblings('.acc__input-error').html('This field is required.');
        }else{
            $(this).siblings('.acc__input-error').html('');
        }

        if(errorCount > 0){
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg.theLoader').fadeOut('fast');

            $('#bookBarCodeWrap').fadeOut('fast', function(){
                $('input', this).val('');
                $('#saveBookBtn').fadeOut();
            });
        }else{
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg.theLoader').fadeOut('fast');

            $('#bookBarCodeWrap').fadeIn('fast', function(){
                $('input', this).val('');
                $('#saveBookBtn').fadeOut();
            });
        }
    });

    $('#scanBarCodeBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var book_bar_code = $('#book_bar_code').val();

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg.theLoader').fadeIn('fast');
        $('#saveBookBtn').fadeOut();

        if(book_bar_code != ''){
            $.ajax({
                method: 'POST',
                url: route('library.books.validate.barcode'),
                data: {barcode : book_bar_code},
                dataType: 'json',
                async: false,
                enctype: 'multipart/form-data',
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (res, textStatus, xhr) {
                    $theBtn.removeAttr('disabled');
                    $theBtn.find('svg.theLoader').fadeOut();
                    $('.error-isbn_no').html('');
                    if(xhr.status == 200 && res.suc == 1){
                        $('#saveBookBtn').fadeIn();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $theBtn.removeAttr('disabled');
                    $theBtn.find('svg.theLoader').fadeOut();
                    $('#saveBookBtn').fadeOut();
                    if(jqXHR.status == 422){
                        $('.error-book_bar_code').html('Barcode already exist. Please try a new one.');
                    }
                },
            });
        }else{
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg.theLoader').fadeOut();
            $('#saveBookBtn').fadeOut();
            $('.error-book_bar_code').html('This field is required.');
        }
    });

    $('#addAmazonBookInfoForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('addAmazonBookInfoForm');

        $('#saveBookBtn').attr('disabled', 'disabled');
        $('#saveBookBtn svg.theLoader').fadeIn();

        let form_data = new FormData(form);
        if($('#book_image').length > 0){
            form_data.append('file', $('#book_image')[0].files[0]); 
        }
        axios({
            method: "post",
            url: route('library.books.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#saveBookBtn').removeAttr('disabled');
            $('#saveBookBtn svg.theLoader').fadeOut();
            if (response.status == 200) {

                successModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('WOW!');
                    $('#successModal .successModalDesc').html('Book successfully stored. If you want to add a new book on the same location then click on <strong>Create Again</strong> butoon else close the modal.');
                });
            }
        }).catch(error => {
            $('#saveBookBtn').removeAttr('disabled');
            $('#saveBookBtn svg.theLoader').fadeOut();
            if(error.response){
                if(error.response.status == 422){
                    warningModal.show();
                    document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                        $('#warningModal .warningModalTitle').html('Oops!');
                        $('#warningModal .warningModalDesc').html('Something went wrong. Please try again later or contact with the administrator.');
                    });

                    setTimeout(() => {
                        warningModal.hide();
                    }, 2000);
                }else{
                    console.log('error');
                }
            }
        });
    });

    $('#successModal .successCloserBtn').on('click', function(e){
        e.preventDefault();
        successModal.hide();
        window.location.reload();
    });

    $('#successModal .successInsertBtn').on('click', function(e){
        e.preventDefault();
        successModal.hide();

        $('#bookBarCodeWrap').fadeOut('fast', function(){
            $('input', this).val('');
            $('#saveBookBtn').fadeOut();
            $('.error-book_bar_code'. this).html('');
        });
        $('#amazonAddBooksTable').html('');
        $('#isbnBooksWrap').fadeOut();
        $('#isbn_no').val('');
    });
})()