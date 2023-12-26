<table style="width: 100%;" class="table table-striped">
    @foreach ($dataList as $item)
    <thead>
        <tr>
            <th style="font-family: 'Roboto-Bold';">{{ $item['month'] }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $iCount=0;
        @endphp
        @foreach ($item["dataArray"] as $normalItem)
        @php
            $iCount++;
        @endphp
        <tr>
            <td>{{ $normalItem['name'] }}</td>
            <td>{{ $normalItem['works_no'] }}</td>
            <td>{{ $normalItem['gender'] }}</td>
            <td>{{ $normalItem['date_of_birth'] }}</td>
            <td>{{ $normalItem['age'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="font-family: 'Roboto-Bold';">Total Employees: </td>
            <td colspan="2" style="font-family: 'Roboto-Bold';"> {{$iCount}} </td>
        </tr>
        <tr></tr>
    </tfoot>
    @endforeach
</table>