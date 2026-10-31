<p>Hello,</p>

@if (!empty($hasAttachment))
    <p>Your paddy prices export is attached ({{ number_format((int) ($rowCount ?? 0)) }} row(s)).</p>
@else
    <p>No paddy price rows matched the selected filters, so no file was attached.</p>
@endif

<p><strong>Filters</strong></p>
<ul>
    @foreach (($filters ?? []) as $label => $value)
        <li>{{ $label }}: {{ $value }}</li>
    @endforeach
</ul>

<p>Regards,<br>SNTC Team</p>
