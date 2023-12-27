<table style="width: 100%;" class="table table-striped">
    @foreach ($dataList['data'] as $item)
    <thead>
        <tr>
            <th style="font-family: 'Roboto-Bold';">{{ $item['year'] }} Years</th>
        </tr>
        <tr>           
            <th style="font-family: 'Roboto-Bold';" data-priority="1" scope="col">Name</th>
            <th style="font-family: 'Roboto-Bold';" data-priority="2" scope="col">Started On</th>
            <th style="font-family: 'Roboto-Bold';" data-priority="3" scope="col">Ended On</th>
            <th style="font-family: 'Roboto-Bold';" data-priority="4" scope="col">Length of Service</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($item["dataArray"] as $normalItem)
        <tr>
            <td>{{ $normalItem['name'] }}</td>
            <td>{{ $normalItem['started_on'] }}</td>
            <td>{{ $normalItem['ended_on'] }}</td>
            <td>{{ $normalItem['length'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr></tr>
    </tfoot>
    @endforeach
</table>