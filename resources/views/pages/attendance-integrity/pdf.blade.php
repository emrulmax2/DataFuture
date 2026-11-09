<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 22px 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #0f172a; }
        h1 { font-size: 14px; margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 1px 6px 1px 0; vertical-align: top; font-size: 8px; }
        .meta td:first-child { color: #64748b; width: 120px; }
        .data { margin-top: 10px; }
        .data th { background: #f1f5f9; text-align: left; font-size: 7px; padding: 4px; border: 1px solid #e2e8f0; }
        .data td { padding: 3px 4px; border: 1px solid #e2e8f0; vertical-align: top; word-wrap: break-word; }
        .note { margin-top: 8px; color: #92400e; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <table class="meta">
        @foreach($header as $line)
            @if(($line[0] ?? '') !== 'Report')
                <tr><td>{{ $line[0] ?? '' }}</td><td>{{ $line[1] ?? '' }}</td></tr>
            @endif
        @endforeach
    </table>
    @if($truncated)
        <div class="note">This PDF shows the first {{ count($rows) }} of {{ number_format($truncated) }} rows. Export to Excel or CSV for all of them.</div>
    @endif
    <table class="data">
        <thead><tr>@foreach($headings as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
            @forelse($rows as $row)
                <tr>@foreach($row as $cell)<td>{{ is_bool($cell) ? ($cell ? 'Yes' : 'No') : \Illuminate\Support\Str::limit((string) $cell, 400) }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($headings) }}">No rows for these filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
