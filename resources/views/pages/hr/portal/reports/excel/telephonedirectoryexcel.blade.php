<table style="width: 100%;" class="table table-striped">
    @foreach ($dataList as $item)
    <thead>
        <tr>
            <th style="font-family: 'Roboto-Bold';">{{ $item['firstcha'] }}</th>
        </tr>      
        <tr>           
            <th style="font-family: 'Roboto-Bold';" data-priority="1" scope="col">Name</th>
            <th style="font-family: 'Roboto-Bold';" data-priority="2" scope="col">Telephone</th>
            <th style="font-family: 'Roboto-Bold';" data-priority="3" scope="col">Mobile</th>
            <th style="font-family: 'Roboto-Bold';" data-priority="4" scope="col">Email</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($item["dataArray"] as $normalItem)
        <tr>
            <td>{{ $normalItem['name'] }}</td>
            <td>{{ $normalItem['telephone'] }}</td>
            <td>{{ $normalItem['mobile'] }}</td>
            <td>{{ $normalItem['email'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr></tr>
    </tfoot>
    @endforeach
</table>