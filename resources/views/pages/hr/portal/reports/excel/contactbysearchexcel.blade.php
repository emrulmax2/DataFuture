<table style="width: 100%;" class="table table-striped">
    @foreach ($dataList['data'] as $item)
    <thead>
    </thead>
    <tbody>
        <tr>
            <td colspan="2"><b>Name</b> </td>
            <td colspan="4"> {{$item['name']}} </td>

            <td colspan="2"><b>E. Telephone</b> </td>
            <td colspan="4"> {{$item['emergency_telephone'] }} </td>
        </tr>
        <tr>
            <td colspan="2"><b>Address</b> </td>
            <td colspan="4"> {{$item['address']}} </td>
            
            <td colspan="2"><b>E. Mobile</b> </td>
            <td colspan="4"> {{$item['emergency_mobile']}} </td>
        </tr>
        <tr>
            <td colspan="2"><b>Post Code</b> </td>
            <td colspan="4"> {{$item['post_code']}} </td>
            
            <td colspan="2"><b>E. Email</b> </td>
            <td colspan="4"> {{$item['emergency_email']}} </td>
        </tr>

        <tr>
            <td colspan="2"><b>Telephone</b> </td>
            <td colspan="4"> {{$item['telephone']}} </td>
        </tr>
        <tr>
            <td colspan="2"><b>Mobile</b> </td>
            <td colspan="4"> {{$item['mobile']}} </td>
        </tr>

        <tr>
            <td colspan="2"><b>Email</b> </td>
            <td colspan="4"> {{$item['email']}} </td>
        </tr>
        <tr></tr>
    </tbody>
    @endforeach
</table>