<p>Hello,</p>

@if (!empty($hasAttachment))
    <p>Your Paddy Price Data has been successfully downloaded from the SNTC platform.</p>
@else
    <p>No Paddy Price Data matched the selected criteria on the SNTC platform. No file has been attached.</p>
@endif

<p>Please find the download details below:</p>

<p>
    Date Range: {{ $filters['Date Range'] ?? 'All' }}<br>
    Type: {{ $filters['Type'] ?? 'All' }}<br>
    Seed: {{ $filters['Seed'] ?? 'All' }}
</p>

@if (!empty($hasAttachment))
    <p>Downloaded data is attached to this email.</p>
@endif

<p>Thank you for using the SNTC platform.</p>
