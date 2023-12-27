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
            }
        </style>
    </head>
    <!-- END: Head -->

    <body style="margin:0; background-color: white; color:rgb(30, 41, 59); padding: 30px;">
        <div style="font-size:24px; font-family:Roboto-Bold; margin-top:10px">
            <span style="">Employee Starter Report</span> <span style="float:right;">London Churchill College</span>
        </div>
        <div style="clear:both"></div>
        <div class="" data-pattern="priority-columns" style="margin-top: 20px">
            <table style="width: 100%;" class="table table-striped">
                <thead style="font-family:Roboto-Bold;font-size:12px;">
                    <tr>
                        <th data-priority="1" scope="col">Surname</th>
                        <th data-priority="2" scope="col">Fore Name</th>
                        <th data-priority="3" scope="col">Works Number</th>
                        <th data-priority="4" scope="col">Start Date</th>
                    </tr>
                </thead>
                <tbody style="font-size:9px;">
                    @foreach ($dataList as $item)
                    <tr>
                        <td>{{ $item['last_name'] }}</td>
                        <td>{{ $item['first_name'] }}</td>
                        <td>{{ $item['works_number'] }}</td>
                        <td>{{ $item['started_on'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </body>
</html>