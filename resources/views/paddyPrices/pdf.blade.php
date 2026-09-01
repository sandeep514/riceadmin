<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Paddy Prices</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2 { margin: 0 0 4px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Paddy Prices</h2>
    <div class="meta">
        @if(!empty($from) || !empty($to))
            Date range:
            {{ $from ?: 'start' }}
            to
            {{ $to ?: 'end' }}
        @else
            All dates
        @endif
        &nbsp;|&nbsp; Generated {{ now()->format('Y-m-d H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Mandi</th>
                <th>State</th>
                <th>Quality</th>
                <th>Crop Year</th>
                <th>Hand Cutting</th>
                <th>Machine Cutting</th>
                <th>Moisture</th>
                <th>Arrivals</th>
                <th>Change</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paddyPrices as $index => $paddyPrice)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ optional($paddyPrice->getMandi_rel)->mandi }}</td>
                    <td>{{ optional($paddyPrice->getState_rel)->state }}</td>
                    <td>
                        @if(optional($paddyPrice->quality_rel)->quality)
                            {{ $paddyPrice->quality_rel->type_label }} - {{ $paddyPrice->quality_rel->quality }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $paddyPrice->crop_year }}</td>
                    <td>{{ $paddyPrice->hand_cutting_price }}</td>
                    <td>{{ $paddyPrice->machine_cutting_price }}</td>
                    <td>{{ $paddyPrice->moisture }}</td>
                    <td>{{ $paddyPrice->total_arrivals }}</td>
                    <td>{{ $paddyPrice->change }}</td>
                    <td>{{ $paddyPrice->created_at ? $paddyPrice->created_at->format('Y-m-d') : '' }}</td>
                    <td>{{ $paddyPrice->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
