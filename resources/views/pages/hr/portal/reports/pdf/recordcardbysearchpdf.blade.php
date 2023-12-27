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
            <span style="">Employee Record Card</span> <span style="float:right;">London Churchill College</span>
        </div>
        <div style="clear:both"></div>
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                @foreach ($returnData['data'] as $item)
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">Record Card For {{ $item['title'].' '.$item['full_name'] }}</div>
   
                <div style="width:100%; float:left; font-family: Roboto-Bold;">Personal Details</div>
                <div style="clear:both"></div>
                
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Title</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['title'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Date of Birth</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['dob'] }}</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Surname</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['last_name'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Ethnic Origin</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['ethnicity'] }}</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Forename</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['first_name'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Nationality</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['nationality'] }}</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Gender</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['gender'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">NI Number</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['ni_number'] }}</div>
                <div style="clear:both"></div>
                <hr style="width:100%;text-align:left;margin-left:0">
                <div style="float:left; font-family: Roboto-Bold;">Employment Details</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Company Name</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">London Churchill College</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Started On</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['started_on'] }}</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Work No</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['works_number'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Ended On</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['end_to'] }}</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Job Title</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['job_title'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Grade</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['job_title'] }}</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Emergency Telephone</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['emergency_telephone'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Emergency Mobile</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['emergency_mobile'] }}</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Current Status</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['job_status'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Emergency Email</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['emergency_email'] }}</div>
                <div style="clear:both"></div>
                <hr style="width:100%;text-align:left;margin-left:0">
                <div style="float:left; font-family: Roboto-Bold;">Contact Information</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Address</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['address'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Telephone</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['telephone'] }}</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Mobile</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['mobile'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Email</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['email'] }}</div>
                <div style="clear:both"></div>
                <hr style="width:100%;text-align:left;margin-left:0">
                <div style="float:left; font-family: Roboto-Bold;">Other Details</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Disabled</div>
                <div style="float:left; width: 20%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $item['disability'] }}</div>                      
                <div style="float:left; width: 20%; color:#696969;font-family: Roboto-Medium;">Car Reg.</div>
                <div style="float:left; width: 20%; margin-left: 5px; font-family: Roboto-Bold;">{{ $item['car_reg'] }}</div>
                <div style="clear:both"></div>
                <hr style="width:100%;text-align:left;margin-left:0">
                @endforeach
            </div>
        </div>     
    </body>
</html>