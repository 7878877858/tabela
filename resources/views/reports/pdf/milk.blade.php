<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $data['title'] ?? 'Report' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #555; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        @media print { body { margin: 12px; } }
    </style>
</head>
<body onload="window.print()">
    <h1>{{ $data['title'] ?? 'Farm Report' }}</h1>
    <div class="meta">
        Period: {{ $filters['from_date'] ?? '' }} — {{ $filters['to_date'] ?? '' }}
        @if(!empty($filters['animal_type'])) | Animal: {{ $filters['animal_type'] }} @endif
    </div>
    @include('reports.partials._dynamic_body', ['data' => $data, 'filters' => $filters])
</body>
</html>
