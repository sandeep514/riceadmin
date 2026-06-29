<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
    <div style="margin:50px auto;width:70%;padding:20px 0">
        <div style="border-bottom:1px solid #eee">
            <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">SNTC Rice Live Pricing App</a>
        </div>

        <p style="font-size:1.1em">Hi,</p>
        <p>
            A new <strong>web brand</strong> has been submitted on the SNTC web portal.
            Please review and activate it from the admin panel if appropriate.
        </p>

        <p><strong>Brand details</strong><br>
            Brand ID: {{ $data['brandId'] ?? '—' }}<br>
            Name: {{ $data['brandName'] ?? '—' }}<br>
            Quality: {{ $data['qualityName'] ?? '—' }}<br>
            Brand year: {{ $data['brandYear'] ?? '—' }}<br>
            Address: {{ $data['address'] ?? '—' }}<br>
            Product mode: {{ $data['productMode'] ?? '—' }}<br>
            Description: {{ $data['description'] ?? '—' }}<br>
            Status: {{ $data['statusLabel'] ?? '—' }}<br>
            @if(!empty($data['logoUrl']))
                Logo: <a href="{{ $data['logoUrl'] }}">{{ $data['logoUrl'] }}</a><br>
            @else
                Logo: —<br>
            @endif
        </p>

        <p><strong>Submitted by</strong><br>
            User ID: {{ $data['userId'] ?? '—' }}<br>
            @if(!empty($data['userName']))
                User name: {{ $data['userName'] }}<br>
            @endif
            @if(!empty($data['userEmail']))
                User email: {{ $data['userEmail'] }}<br>
            @endif
            Date &amp; time: {{ $data['submittedAt'] ?? '—' }}
        </p>

        <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
        <hr style="border:none;border-top:1px solid #eee" />
    </div>
</div>
