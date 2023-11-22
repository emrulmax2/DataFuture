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
        <style type="text/css">
            @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
            @page {
                margin: 0;
            }
            * { padding: 0; margin: 0; }
     
            body{
                font-family: 'Roboto', sans-serif;          
            }

            .table tr,.table td {
                height: 20px;
            }

            .table>tbody>tr>td
            {
                padding:3px; 
            }

            .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th
            {
                padding:5px; 
            }
        </style>
    </head>
    <!-- END: Head -->
    <body style="margin:0; background-color: white; padding:30px;">
        <div style="text-align: center">
            <span style="font-size:18px; color:rgb(128, 128, 128);  margin-top:10px;">Feed Attendance List</span>
        </div> 
        <div style="clear:both"></div>
        <div class="" data-pattern="priority-columns" style="margin-top: 10px">
            <table style="width: 100%;" class="table table-bordered">
                <thead style="font-size:12px;">
                    <tr>
                        <th data-priority="1" scope="col">Class Plan ID</th>
                        <th data-priority="2" scope="col">Term</th>
                        <th data-priority="3" scope="col">Course Name</th>
                        <th data-priority="4" scope="col">Module Name</th>
                        <th data-priority="5" scope="col">Group</th>
                    </tr>
                    <tbody style="font-size:9px; text-align:center">
                        <tr>
                            <td>{{ $data["plan_id"] }}</td>
                            <td>{{$semester->name }}</td>
                            <td>{{ $data["course"] }}</td>
                            <td>{{ $data["module"] }}</td>
                            <td>{{ $data["group"] }}</td>
                        </tr>
                    </tbody>
                </thead>
            </table>
        </div>

        <div style="clear:both"></div>
        <div class="" data-pattern="priority-columns" style="margin-top: 5px">
            <table style="width: 100%;" class="table table-bordered">
                <thead style="font-size:12px;">
                    <tr>
                        <th data-priority="1" scope="col">Tutor name</th>
                        <th data-priority="2" scope="col">Time</th>
                        <th data-priority="3" scope="col">Room</th>
                        <th data-priority="4" scope="col">Date</th>
                    </tr>
                    <tbody style="font-size:9px; text-align:center">
                        <tr>
                            <td>{{ $data["tutor"] }}</td>
                            <td>{{ $data["start_time"] }} - {{ $data["end_time"] }}</td>
                            <td>{{ $data["room"] }}</td>
                            <td>{{ $data["date"] }}</td>
                        </tr>
                    </tbody>
                </thead>
            </table>
        </div>

        <div style="clear:both"></div>
        <div class="" data-pattern="priority-columns" style="margin-top: 5px">
            <table style="width: 100%;" class="table table-bordered">
                <thead style="font-size:12px;">
                    <tr>
                        <th data-priority="1" scope="col">Registration No</th>
                        <th data-priority="2" scope="col">Student Name</th>
                        <th data-priority="3" scope="col">Attendance</th>
                    </tr>
                    <tbody style="font-size:9px">
                        @foreach($data["assignStudentList"] as $list) 
                        <tr>
                            <td>{{ $list->student->registration_no }}</td>
                            <td>{{ $list->student->full_name }}</td>
                            <td>{{ $attendanceFeedByAttendance->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </thead>
            </table>
        </div>
    </body>
</html>