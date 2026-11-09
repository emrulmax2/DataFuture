{{-- The term timetable PDF's page header: the crest and college name, and $right (HTML, already escaped). --}}
@php $crest = resource_path('images/pdf/lcc-crest.png'); @endphp
<table class="hd"><tr>
    <td class="brand">@if(is_file($crest))<img src="{{ $crest }}" alt="">@endif<span>London<br>Churchill<br>College</span></td>
    <td class="right">{!! $right !!}</td>
</tr></table>
