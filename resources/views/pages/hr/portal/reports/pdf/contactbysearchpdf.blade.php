<!DOCTYPE html>
<html lang="en" class="">
    <!-- BEGIN: Head -->
    <head>
        <meta charset="utf-8">
        <!-- BEGIN: CSS Assets-->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <!-- END: CSS Assets-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <style>
            html { margin: 0px}
            @page { margin: 0px; }
            @font-face {
                font-family: 'Roboto-Regular';
                src: url({{ storage_path('fonts\Roboto-Regular.ttf') }}) format("truetype"); 
                font-style: normal;
            }
            
            @font-face {
                font-family: 'Roboto-Medium';
                src: url({{ storage_path('fonts\Roboto-Medium.ttf') }}) format("truetype"); 
                font-style: normal;
            }

            @font-face {
                font-family: 'Roboto-Bold';
                src: url({{ storage_path('fonts\Roboto-Bold.ttf') }}) format("truetype"); 
                font-style: normal;
            }

            body {
                font-size:10px;
            }
        </style>
    </head>
    <!-- END: Head -->

    <body style="margin:0; background-color: white; color:rgb(30, 41, 59); padding: 30px;">
        <div style="font-size:24px; font-family:Roboto-Bold; margin-top:10px">
            <span style="">Employee Contact Details</span> <span style="float:right;">London Churchill College</span>
        </div>
        <div style="clear:both"></div>
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                @foreach ($returnData['data'] as $item)
                <div style="float:left; width: 11%; color:#696969;font-family: Roboto-Medium;">Name</div>
                <div style="float:left; width: 14%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['name'] }}</div>                      
                <div style="float:left; width: 11%; color:#696969;font-family: Roboto-Medium;">Post Code</div>
                <div style="float:left; width: 23%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['post_code'] }}</div> 
                
                <div style="float:left; width: 18%; color:#696969;font-family: Roboto-Medium;">E. Telephone</div>
                <div style="float:left; width: 23%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['emergency_telephone'] }}</div> 
                <div style="clear:both"></div>                    

                 
                <div style="float:left; width: 11%; color:#696969;font-family: Roboto-Medium;">Telephone</div>
                <div style="float:left; width: 14%; margin-left: 5px;margin-right: 5px;font-family: Roboto-Bold;">{{ $item['telephone'] }}</div>
                <div style="float:left; width: 11%; color:#696969;font-family: Roboto-Medium;">Address</div>
                <div style="float:left; width: 23%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['address'] }}</div>
                <div style="float:left; width: 18%; color:#696969;font-family: Roboto-Medium;">E. Mobile</div>
                <div style="float:left; width: 23%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['emergency_mobile'] }}</div>
                <div style="clear:both"></div>        

                <div style="float:left; width: 11%; color:#696969;font-family: Roboto-Medium;">Mobile</div>
                <div style="float:left; width: 14%; margin-left: 5px; margin-right: 5px;font-family: Roboto-Bold;">{{ $item['mobile'] }}</div> 
                
                <div style="float:left; width: 11%; color:#696969;font-family: Roboto-Medium;">Email</div>
                <div style="float:left; width: 23%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['email'] }}</div>               
                                     

                <div style="float:left; width: 18%; color:#696969;font-family: Roboto-Medium;">E. Email</div>
                <div style="float:left; width: 23%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['emergency_email'] }}</div>
                <div style="clear:both"></div>
                <hr style="width:100%;text-align:left;margin-left:0">
                @endforeach
            </div>
        </div>     
    </body>
</html>