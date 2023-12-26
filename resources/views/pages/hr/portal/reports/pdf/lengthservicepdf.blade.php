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
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    </head>
    <!-- END: Head -->

    <body style="margin:0; background-color: white; color:rgb(30, 41, 59); padding: 30px;">
        <div style="font-size:24px;  margin-top:10px">
            <span style="">Employee Service Lengths</span> <span style="float:right;">London Churchill College</span>
        </div>
        <div style="clear:both"></div>
        
        @foreach ($dataList as $item)
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title">
                    <div style="font-size:12px;  margin-top:5px">
                        <span style="">{{ $item['year'] }} Years</span>
                    </div>  
                </div>
                <div style="clear:both"></div>
                <div class="" data-pattern="priority-columns" style="margin-top: 10px">
                    <table style="width: 100%;" class="table table-striped">
                        <thead style="font-size:12px;">
                            <tr>
                                <th data-priority="1" scope="col">Name</th>
                                <th data-priority="2" scope="col">Started On</th>
                                <th data-priority="3" scope="col">Ended On</th>
                                <th data-priority="4" scope="col">Length of Service</th>
                            </tr>
                        </thead>
                        <tbody style="font-size:9px;">
                            @foreach ($item["dataArray"] as $normalItem)
                            <tr>
                                <td>{{ $normalItem['name'] }}</td>
                                <td>{{ $normalItem['started_on'] }}</td>
                                <td>{{ $normalItem['ended_on'] }}</td>
                                <td>{{ $normalItem['length'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>        
            </div>
        </div>
        @endforeach
    </body>
</html>