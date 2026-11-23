{{-- Label · bar (share of the accepted students) · count · percent. Muted rows (Other, Not recorded) are grey. --}}
<table class="aar-share">
    @foreach($rows as $row)
        <tr class="{{ $row['muted'] ? 'is-muted' : '' }}">
            <td class="aar-share__label">{{ $row['label'] }}</td>
            <td class="aar-share__barcell">
                <div class="aar-share__track"><div class="aar-share__fill" style="width: {{ min(100, $row['pct']) }}%;"></div></div>
            </td>
            <td class="aar-share__count">{{ $row['count'] }}</td>
            <td class="aar-share__pct">{{ $row['pct'] }}%</td>
        </tr>
    @endforeach
</table>
