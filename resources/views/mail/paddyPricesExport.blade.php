<p>Hello,</p>

@if (!empty($hasAttachment))
    <p>Please find your paddy prices export attached ({{ number_format((int) ($rowCount ?? 0)) }} record(s)).</p>
@else
    <p>No paddy price records match the selected criteria. No file has been attached.</p>
@endif

<p><strong>Filters</strong></p>
<ul>
    @foreach (($filters ?? []) as $label => $value)
        <li>{{ $label }}: {{ $value }}</li>
    @endforeach
</ul>

<p>Regards,<br>SNTC Team</p>
