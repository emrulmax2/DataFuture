import { createIcons, icons } from "lucide";

/* Profile Menu Start */
if($('.liveStudentMainMenu').length > 0){
    $('.liveStudentMainMenu li.hasChildren > a').on('click', function(e){
        e.preventDefault();
        var $this = $(this);

        if($this.hasClass('active')){
            $this.removeClass('active');
            $this.siblings('.liveStudentSubMenu').removeClass('show');
            $('.liveStudentMainMenu').animate({'padding-bottom' : '0'}, 'fast');
        }else{
            $this.parent('li').siblings('li').children('a').removeClass('active');
            $this.parent('li').siblings('li').children('.liveStudentSubMenu').removeClass('show');

            $this.addClass('active');
            $('.liveStudentMainMenu').animate({'padding-bottom' : '55px'}, 350, function(){
                $this.siblings('.liveStudentSubMenu').addClass('show');
            });
        }
    })
}

if($('.doitOnlineSecondBoxToggle').length > 0) {
    $(".doitOnlineSecondBoxToggle").on('click', function(e){
        e.preventDefault();
        $("#doitOnlineSecondBox").toggle("slow");
      });
}
/* Profile Menu End */