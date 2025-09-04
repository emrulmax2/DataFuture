
(function(){
    $(document).on('click', '.downloadEventDoc', function(e){
        e.preventDefault();
        let $theLink = $(this);
        let docId = $theLink.attr('data-docid');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('news.updates.document.download'), 
            data: {row_id : docId},
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