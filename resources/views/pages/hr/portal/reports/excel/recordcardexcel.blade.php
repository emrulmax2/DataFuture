<table style="width: 100%;" class="table table-striped">
    @foreach ($dataList as $item)
    <thead>
        <tr>
            <th style="font-family: 'Roboto-Bold';">Record Card For {{ $item['title'].' '.$item['full_name'] }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="font-family: 'Roboto-Bold';">Personal Details</td>
        </tr>
        <tr>
            <td colspan="2"><b>Title</b> </td>
            <td colspan="4"> {{$item['title']}} </td>

            <td colspan="2"><b>Date of Birth</b> </td>
            <td colspan="4"> {{$item['dob'] }} </td>
        </tr>
        <tr>
            <td colspan="2"><b>Surname</b> </td>
            <td colspan="4"> {{$item['last_name']}} </td>
            
            <td colspan="2"><b>Ethnic Origin</b> </td>
            <td colspan="4"> {{$item['ethnicity']}} </td>
        </tr>
        <tr>
            <td colspan="2"><b>Forename</b> </td>
            <td colspan="4"> {{$item['first_name']}} </td>
            
            <td colspan="2"><b>Nationality</b> </td>
            <td colspan="4"> {{$item['nationality']}} </td>
        </tr>

        <tr>
            <td colspan="2"><b>Gender</b> </td>
            <td colspan="4"> {{$item['gender']}} </td>

            <td colspan="2"><b>NI Number</b> </td>
            <td colspan="4"> {{$item['ni_number']}} </td>
        </tr>
        <tr></tr>
        <tr>
            <td style="font-family: 'Roboto-Bold';">Employment Details</td>
        </tr>
        <tr>
            <td colspan="2"><b>Company Name</b> </td>
            <td colspan="4"> London Churchill College </td>

            <td colspan="2"><b>Started On</b> </td>
            <td colspan="4"> {{$item['started_on'] }} </td>
        </tr>
        <tr>
            <td colspan="2"><b>Work No</b> </td>
            <td colspan="4"> {{ $item['works_number'] }} </td>

            <td colspan="2"><b>Ended On</b> </td>
            <td colspan="4"> {{$item['end_to'] }} </td>
        </tr>
        <tr>
            <td colspan="2"><b>Job Title</b> </td>
            <td colspan="4"> {{ $item['job_title'] }}</td>

            <td colspan="2"><b>Grade</b> </td>
            <td colspan="4"> {{$item['job_title'] }} </td>
        </tr>
        <tr>
            <td colspan="2"><b>Emergency Telephone</b> </td>
            <td colspan="4"> {{ $item['emergency_telephone'] }}</td>

            <td colspan="2"><b>Emergency Mobile</b> </td>
            <td colspan="4"> {{$item['emergency_mobile'] }} </td>
        </tr>
        <tr>
            <td colspan="2"><b>Current Status</b> </td>
            <td colspan="4"> {{ $item['job_status'] }}</td>

            <td colspan="2"><b>Emergency Email</b> </td>
            <td colspan="4"> {{$item['emergency_email'] }} </td>
        </tr>
        <tr></tr>
        <tr>
            <td style="font-family: 'Roboto-Bold';">Contact Information</td>
        </tr>
        <tr>
            <td colspan="2"><b>Address</b> </td>
            <td colspan="4"> {{ $item['address'] }} </td>

            <td colspan="2"><b>Telephone</b> </td>
            <td colspan="4"> {{$item['telephone'] }} </td>
        </tr>
        <tr>
            <td colspan="2"><b>Mobile</b> </td>
            <td colspan="4"> {{ $item['mobile'] }} </td>

            <td colspan="2"><b>Email</b> </td>
            <td colspan="4"> {{$item['email'] }} </td>
        </tr>
        <tr></tr>
        <tr>
            <td style="font-family: 'Roboto-Bold';">Other Details</td>
        </tr>
        <tr>
            <td colspan="2"><b>Disabled</b> </td>
            <td colspan="4"> {{ $item['disability'] }} </td>

            <td colspan="2"><b>Car Reg.</b> </td>
            <td colspan="4"> {{$item['car_reg'] }} </td>
        </tr>
        <tr></tr>
    </tbody>
    @endforeach
</table>