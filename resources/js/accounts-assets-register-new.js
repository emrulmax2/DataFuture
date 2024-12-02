import TomSelect from "tom-select";

(function(){
    let accTomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };


    // $('#newAssetsRegTable .assetsRegTom').each(function(){
    //     new TomSelect(this, accTomOptions);
    // })

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    const successModalEl = document.getElementById('successModal')
    successModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#successModal button.successCloser').attr('data-action', 'none').attr('data-redirect', 'NONE');
    });

    $(document).on('click', '.successCloser', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            if($(this).attr('data-redirect') != 'NONE'){
                window.location.href = $(this).attr('data-redirect');
            }else{
                window.location.reload();
            }
        }else{
            successModal.hide();
        }
    })

    $('#newAssetsRegTable').on('keyup paste', '.description', function(){
        var $theInput = $(this);
        var $theRow = $theInput.closest('.assets_row');
        var $theType = $theRow.find('.acc_asset_type_id');

        if($theInput.val() != '' && $theType.val() > 0){
            $theRow.find('.save_row').removeAttr('disabled');
        }else{
            $theRow.find('.save_row').attr('disabled', 'disabled');
        }
    });

    $('#newAssetsRegTable').on('change', '.acc_asset_type_id', function(){
        var $theSelect = $(this);
        var $theRow = $theSelect.closest('.assets_row');
        var $theDescription = $theRow.find('.description');

        if($theDescription.val() != '' && $theSelect.val() > 0){
            $theRow.find('.save_row').removeAttr('disabled');
        }else{
            $theRow.find('.save_row').attr('disabled', 'disabled');
        }
    });

    $('#newAssetsRegTable').on('click', '.save_row', function(){
        var $theBtn = $(this);
        var the_id = $theBtn.attr('data-id');
        var $theRow = $('#newAssetsRegTable').find('#assets_row_'+the_id);

        var newAssests = parseInt($(document).find('.assetsRegCounter').attr('data-count'), 10);

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg.iconSave').fadeOut();
        $theBtn.find('svg.iconLoading').fadeIn();

        var description = $theRow.find('.description').val();
        var acc_asset_type_id = $theRow.find('.acc_asset_type_id').val();
        var location = $theRow.find('.location').val();
        var serial = $theRow.find('.serial').val();
        var barcode = $theRow.find('.barcode').val();
        var life = $theRow.find('.life').val();
        if(description != '' && acc_asset_type_id > 0){
            axios({
                method: "post",
                url: route('accounts.assets.register.update'),
                data: {
                    id: the_id, 
                    description: description, 
                    acc_asset_type_id: acc_asset_type_id, 
                    location: location, 
                    serial: serial, 
                    barcode: barcode, 
                    life: life, 
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.iconLoading').fadeOut();
                $theBtn.find('svg.iconSave').fadeIn();
                
                if (response.status == 200) {
                    $theRow.remove();
                    var leftAssets = (newAssests - 1);
                    if(leftAssets > 0){
                        $(document).find('.assetsRegCounter').attr('data-count', leftAssets).html(leftAssets);
                    }else{
                        $(document).find('.assetsRegCounter').remove();
                    }

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Assets Register entry successfully updated.');
                        $('#successModal .successCloser').attr('data-action', 'NONE').attr('data-redirect', '');
                    });

                    setTimeout(() => {
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.iconLoading').fadeOut();
                $theBtn.find('svg.iconSave').fadeIn();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $theBtn.attr('disabled', 'disabled');
            $theBtn.find('svg.iconLoading').fadeOut();
            $theBtn.find('svg.iconSave').fadeIn();

            if(description == ''){
                $theRow.find('.description').siblings('.acc__input-error').fadeIn().html('This field is required.');
            }else{
                $theRow.find('.description').siblings('.acc__input-error').fadeOut().html('');
            }
            if(acc_asset_type_id > 0){
                $theRow.find('.acc_asset_type_id').siblings('.acc__input-error').fadeOut().html('');
            }else{
                $theRow.find('.acc_asset_type_id').siblings('.acc__input-error').fadeIn().html('This field is required.');
            }
        }
    });


    $('#newAssetsRegTable').on('click', '.downloadTransDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('accounts.storage.trans.download.link'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let res = response.data.res;
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                if(res != ''){
                    window.open(res, '_blank');
                }
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    });
})()